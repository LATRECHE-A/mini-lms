<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Form request for validating note creation and updates. 
 * Only admins are authorized to make these requests.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'formation_id' => ['required', 'exists:formations,id'],
            'subject' => ['required', 'string', 'max:255'],
            'grade' => ['required', 'numeric', 'min:0', 'max:20'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'grade.max' => 'La note ne peut pas dépasser 20.',
            'grade.min' => 'La note ne peut pas être négative.',
        ];
    }
}
