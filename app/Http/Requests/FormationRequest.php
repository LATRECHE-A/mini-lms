<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Form request class for validating formation data when creating or updating a formation.
 * Only admins are authorized to make these requests.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'level' => ['required', 'in:débutant,intermédiaire,avancé'],
            'duration_hours' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'status' => ['required', 'in:draft,published'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la formation est obligatoire.',
            'level.in' => 'Le niveau doit être débutant, intermédiaire ou avancé.',
        ];
    }
}
