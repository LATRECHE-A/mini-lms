<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Form request for validating chapter data (create/update).
 * Now includes image, sources, and mermaid diagram fields.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChapterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'order' => ['nullable', 'integer', 'min:0'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'image_alt' => ['nullable', 'string', 'max:512'],
            'image_credit' => ['nullable', 'string', 'max:512'],
            'sources_json' => ['nullable', 'string', 'max:10000'],
            'mermaid_code' => ['nullable', 'string', 'max:10000'],
        ];
    }

    /**
     * Massage sources_json into the 'sources' array for the model.
     */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);

        if ($key) {
            return $data;
        }

        // Parse sources JSON string into array
        if (isset($data['sources_json'])) {
            $decoded = json_decode($data['sources_json'], true);
            $data['sources'] = is_array($decoded) ? $decoded : null;
            unset($data['sources_json']);
        }

        // Clean empty strings to null
        foreach (['image_url', 'image_alt', 'image_credit', 'mermaid_code'] as $field) {
            if (isset($data[$field]) && trim($data[$field]) === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'image_url.url' => "L'URL de l'image n'est pas valide.",
            'image_url.max' => "L'URL est trop longue (max 2048 caractères).",
        ];
    }
}
