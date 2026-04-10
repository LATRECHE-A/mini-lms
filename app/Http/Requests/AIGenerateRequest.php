<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Form request for validating AI content generation requests.
 * Ensures the prompt, type, chapter count, depth, and attachments meet specified criteria.
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
        // Ensure defaults if somehow missing
        $this->mergeIfMissing([
            'chapter_count' => 3,
            'depth' => 'standard',
            'type' => 'mixed',
        ]);
    }

    public function rules(): array
    {
        return [
            'prompt'        => ['required', 'string', 'min:10', 'max:5000'],
            'type'          => ['required', 'in:course,quiz,mixed'],
            'chapter_count' => ['required', 'integer', 'between:1,10'],
            'depth'         => ['required', 'in:standard,detailed,exhaustive'],
            'attachments'   => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                if (!$this->hasFile('attachments')) return;

                $allowed   = GeminiFileUploadService::SUPPORTED_MIMES;
                $totalSize = 0;

                foreach ($this->file('attachments') as $i => $file) {
                    $totalSize += $file->getSize();
                    if (!in_array($file->getMimeType(), $allowed)) {
                        $validator->errors()->add("attachments.{$i}", "Type non supporté : « {$file->getClientOriginalName()} ».");
                    }
                }

                if ($totalSize > 25 * 1024 * 1024) {
                    $validator->errors()->add('attachments', 'La taille totale des fichiers ne doit pas dépasser 25 Mo.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'prompt.required'       => 'Le sujet est obligatoire.',
            'prompt.min'            => 'Le sujet doit contenir au moins 10 caractères.',
            'prompt.max'            => 'Le sujet ne doit pas dépasser 5000 caractères.',
            'type.required'         => 'Le type de contenu est obligatoire.',
            'type.in'               => 'Type de contenu invalide.',
            'chapter_count.required'=> 'Le nombre de chapitres est obligatoire.',
            'chapter_count.integer' => 'Le nombre de chapitres doit être un nombre entier.',
            'chapter_count.between' => 'Le nombre de chapitres doit être entre 1 et 10.',
            'depth.required'        => 'Le niveau de détail est obligatoire.',
            'depth.in'              => 'Niveau de détail invalide. Choisissez Standard, Détaillé ou Exhaustif.',
            'attachments.max'       => 'Vous pouvez joindre au maximum 5 fichiers.',
            'attachments.*.max'     => 'Chaque fichier ne doit pas dépasser 10 Mo.',
            'attachments.*.file'    => 'Chaque pièce jointe doit être un fichier valide.',
        ];
    }
}
