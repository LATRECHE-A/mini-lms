<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Validates formation create/update payloads.
 *
 * Authorization is delegated to FormationPolicy so that both admins and
 * students-who-own-the-formation pass the gate. Routes themselves live in
 * separate role-prefixed groups; the policy is the single source of truth.
 */

namespace App\Http\Requests;

use App\Models\Formation;
use Illuminate\Foundation\Http\FormRequest;

class FormationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $formation = $this->route('formation');

        if ($formation instanceof Formation) {
            return $this->user()?->can('update', $formation) ?? false;
        }

        return $this->user()?->can('create', Formation::class) ?? false;
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
