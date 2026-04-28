<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Validates chapter create/update payloads (title, order, image, sources,
 * mermaid). Authorized via FormationPolicy::update on the parent formation
 * - so admins always pass and students pass for formations they own.
 */

namespace App\Http\Requests;

use App\Models\Chapter;
use App\Models\Formation;
use Illuminate\Foundation\Http\FormRequest;

class ChapterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $formation = $this->route('formation');
        if ($formation instanceof Formation) {
            return $user->can('update', $formation);
        }

        $chapter = $this->route('chapter');
        if ($chapter instanceof Chapter && $chapter->formation) {
            return $user->can('update', $chapter->formation);
        }

        return $user->isAdmin();
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

    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);

        if ($key) {
            return $data;
        }

        if (isset($data['sources_json'])) {
            $decoded = json_decode($data['sources_json'], true);
            $data['sources'] = is_array($decoded) ? $decoded : null;
            unset($data['sources_json']);
        }

        foreach (['image_url', 'image_alt', 'image_credit', 'mermaid_code'] as $field) {
            if (isset($data[$field]) && trim((string) $data[$field]) === '') {
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
