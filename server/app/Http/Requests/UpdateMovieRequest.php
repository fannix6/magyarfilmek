<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMovieRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:125|unique:movies,title,' . $this->route('id'),
            'produced' => 'sometimes|nullable|integer',
            'length' => 'sometimes|nullable|string',
            'premiere' => 'sometimes|nullable|string',
            'watchlink' => 'sometimes|nullable|url',
            'imdblink' => 'sometimes|nullable|url',
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'A film címe kötelező mező.',
            'title.string' => 'A film címének szövegnek kell lennie.',
            'title.max' => 'A film címe nem lehet hosszabb 125 karakternél.',
            'title.unique' => 'Ezzel a címmel már létezik film az adatbázisban.',

            'produced.integer' => 'A gyártási évnek számnak kell lennie.',

            'length.string' => 'A film hosszának szövegnek kell lennie.',

            'premiere.string' => 'A premier dátumnak szövegnek kell lennie.',

            'watchlink.url' => 'A film linkje érvényes URL-nek kell legyen.',
            'imdblink.url' => 'Az IMDB link érvényes URL-nek kell legyen.',
        ];
    }
}
