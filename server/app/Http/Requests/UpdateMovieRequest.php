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

}
