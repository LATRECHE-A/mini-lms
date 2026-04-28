<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Validates sub-chapter create/update payloads. Authorized via the parent
 * chapter's formation (FormationPolicy::update) - admins always pass,
 * students pass for formations they own.
 */

namespace App\Http\Requests;

use App\Models\Chapter;
use App\Models\SubChapter;
use Illuminate\Foundation\Http\FormRequest;

class SubChapterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $chapter = $this->route('chapter');
        if ($chapter instanceof Chapter && $chapter->formation) {
            return $user->can('update', $chapter->formation);
        }

        $subchapter = $this->route('subchapter');
        if ($subchapter instanceof SubChapter && $subchapter->chapter?->formation) {
            return $user->can('update', $subchapter->chapter->formation);
        }

        return $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:65000'],
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
        ];
    }
}
