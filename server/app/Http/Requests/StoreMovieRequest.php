<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|unique:movies|string',
            'produced' => 'required|integer',
            'premiere' => 'required|string',
            'watchlink' => 'required|string',
            'imdblink' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            // title
            'title.required' => 'A cím megadása kötelező.',
            'title.string'   => 'A cím csak szöveg lehet.',
            'title.unique'   => 'Ezzel a címmel már létezik egy film.',

            // produced
            'produced.required' => 'A gyártás éve kötelező.',
            'produced.integer'  => 'A gyártás éve csak szám lehet.',

            // premiere
            'premiere.required' => 'A premier dátum kötelező.',
            'premiere.string'   => 'A premier csak szöveg lehet.',

            // watchlink
            'watchlink.required' => 'A link megadása kötelező.',
            'watchlink.string'   => 'A link csak szöveg lehet.',

            // imdblink
            'imdblink.required' => 'Az IMDB link kötelező.',
            'imdblink.string'   => 'Az IMDB link csak szöveg lehet.',
        ];
    }
}
