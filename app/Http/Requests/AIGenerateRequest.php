<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 */

namespace App\Http\Requests;

use App\Services\GeminiFileUploadService;
use Illuminate\Foundation\Http\FormRequest;

class AIGenerateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing([
            'chapter_count' => 3,
            'depth' => 'standard',
            'type' => 'mixed',
        ]);
    }

    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'min:10', 'max:5000'],
            'type' => ['required', 'in:course,quiz,mixed,full'],
            'chapter_count' => ['required', 'integer', 'between:1,10'],
            'depth' => ['required', 'in:standard,detailed,exhaustive'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                if (! $this->hasFile('attachments')) {
                    return;
                }

                $allowed = GeminiFileUploadService::SUPPORTED_MIMES;
                $totalSize = 0;

                foreach ($this->file('attachments') as $i => $file) {
                    $totalSize += $file->getSize();
                    if (! in_array($file->getMimeType(), $allowed)) {
                        $validator->errors()->add("attachments.{$i}", "Type non supporté : « {$file->getClientOriginalName()} ».");
                    }
                }

                if ($totalSize > 25 * 1024 * 1024) {
                    $validator->errors()->add('attachments', 'Taille totale > 25 Mo.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'prompt.required' => 'Le sujet est obligatoire.',
            'prompt.min' => 'Le sujet doit contenir au moins 10 caractères.',
            'type.in' => 'Type de contenu invalide.',
            'chapter_count.between' => 'Nombre de chapitres : entre 1 et 10.',
            'depth.in' => 'Niveau de détail invalide.',
            'attachments.max' => '5 fichiers maximum.',
            'attachments.*.max' => 'Chaque fichier : max 10 Mo.',
        ];
    }
}
