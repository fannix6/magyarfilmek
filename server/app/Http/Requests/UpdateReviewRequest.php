<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'score' => 'sometimes|required|integer|min:1|max:10',
            'opinion' => 'sometimes|required|string|min:3|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'score.required' => 'A pontszam kotelezo.',
            'score.integer' => 'A pontszam csak egesz szam lehet.',
            'score.min' => 'A pontszam minimum 1.',
            'score.max' => 'A pontszam maximum 10.',
            'opinion.required' => 'A velemeny megadasa kotelezo.',
            'opinion.min' => 'A velemeny legalabb 3 karakter legyen.',
            'opinion.max' => 'A velemeny legfeljebb 2000 karakter lehet.',
        ];
    }
}
