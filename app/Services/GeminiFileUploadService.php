<?php

/*
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Service to handle file uploads to Google Gemini for AI content processing.
 * Validates file types and sizes, manages resumable uploads, polls for processing status,
 * and provides cleanup methods for uploaded files.
 */

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiFileUploadService
{
    public const SUPPORTED_MIMES = [
        'application/pdf', 'text/plain', 'text/markdown',
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = trim(config('services.gemini.api_key', ''));
    }

    public function uploadFile(UploadedFile $file): array
    {
        $mime = $file->getMimeType();
        if (!in_array($mime, self::SUPPORTED_MIMES)) throw new \Exception("Type non supporté : {$mime}");
        if ($file->getSize() > 10 * 1024 * 1024) throw new \Exception("Fichier > 10 Mo.");

        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $content  = file_get_contents($file->getRealPath());

        $initResponse = Http::timeout(15)
            ->withHeaders([
                'X-Goog-Upload-Protocol' => 'resumable',
                'X-Goog-Upload-Command'  => 'start',
                'X-Goog-Upload-Header-Content-Length' => strlen($content),
                'X-Goog-Upload-Header-Content-Type'   => $mime,
                'Content-Type' => 'application/json',
                'X-goog-api-key' => $this->apiKey,
            ])
            ->post('https://generativelanguage.googleapis.com/upload/v1beta/files', [
                'file' => ['display_name' => $safeName],
            ]);

        if (!$initResponse->successful()) {
            throw new \Exception("Envoi fichier échoué : " . ($initResponse->json('error.message') ?? $initResponse->body()));
        }

        $uploadUrl = $initResponse->header('X-Goog-Upload-URL');
        if (!$uploadUrl) throw new \Exception("URL upload non reçue.");

        $uploadResponse = Http::timeout(30)
            ->withHeaders(['X-Goog-Upload-Command' => 'upload, finalize', 'X-Goog-Upload-Offset' => '0', 'Content-Type' => $mime])
            ->withBody($content, $mime)->post($uploadUrl);

        if (!$uploadResponse->successful()) {
            throw new \Exception("Transfert échoué : " . ($uploadResponse->json('error.message') ?? ''));
        }

        $fileData = $uploadResponse->json('file') ?? [];
        $fileUri  = $fileData['uri'] ?? null;
        $fileName = $fileData['name'] ?? null;
        if (!$fileUri) throw new \Exception("URI fichier non retourné.");

        $state = $fileData['state'] ?? 'PROCESSING';
        for ($poll = 0; $poll < 3 && $state === 'PROCESSING'; $poll++) {
            sleep(2);
            $status = Http::timeout(10)->withHeaders(['X-goog-api-key' => $this->apiKey])
                ->get("https://generativelanguage.googleapis.com/v1beta/{$fileName}");
            $state = $status->json('state') ?? 'PROCESSING';
        }

        return ['uri' => $fileUri, 'mime_type' => $mime, 'name' => $fileName];
    }

    public function deleteFile(string $fileName): void
    {
        try {
            Http::timeout(10)->withHeaders(['X-goog-api-key' => $this->apiKey])
                ->delete("https://generativelanguage.googleapis.com/v1beta/{$fileName}");
        } catch (\Throwable $e) {
            Log::warning("Failed to delete Gemini file {$fileName}: {$e->getMessage()}");
        }
    }

    public function deleteAll(array $fileInfos): void
    {
        foreach ($fileInfos as $info) {
            if (!empty($info['name'])) $this->deleteFile($info['name']);
        }
    }
}
