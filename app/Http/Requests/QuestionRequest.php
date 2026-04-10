<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Form request for validating question creation and updates. 
 * Only accessible by admins to ensure proper question management in the system.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'question_text' => ['required', 'string', 'max:1000'],
            'answers' => ['required', 'array', 'min:2', 'max:6'],
            'answers.*.text' => ['required', 'string', 'max:500'],
            'correct_answer' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.min' => 'Il faut au moins 2 réponses.',
            'answers.*.text.required' => 'Chaque réponse doit avoir un texte.',
            'correct_answer.required' => 'Vous devez indiquer la bonne réponse.',
        ];
    }
}
