<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Form request for validating personal note data when creating or updating a note.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonalNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStudent();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:10000'],
            'sub_chapter_id' => ['nullable', 'integer', 'exists:sub_chapters,id'],
        ];
    }
}
