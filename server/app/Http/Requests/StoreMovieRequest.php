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
            'title' => 'required|string|max:125|unique:movies,title',
            'produced' => 'nullable|integer',
            'length' => 'nullable|string|max:50',
            'premiere' => 'nullable|string|max:50',
            'watchlink' => 'nullable|url',
            'imdblink' => 'nullable|url',
            'cover' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            // title
            'title.required' => 'A cĂ­m megadĂˇsa kĂ¶telezĹ‘.',
            'title.string'   => 'A cĂ­m csak szĂ¶veg lehet.',
            'title.unique'   => 'Ezzel a cĂ­mmel mĂˇr lĂ©tezik egy film.',

            'produced.integer'  => 'A gyĂˇrtĂˇs Ă©ve csak szĂˇm lehet.',
            'length.string'   => 'A hossz csak szĂ¶veg lehet.',
            'premiere.string'   => 'A premier csak szĂ¶veg lehet.',
            'watchlink.url'   => 'A link csak URL lehet.',
            'imdblink.url'   => 'Az IMDB link csak URL lehet.',
            'cover.string' => 'A borĂ­tĂł mezĹ‘ csak szĂ¶veg lehet.',
            'cover.max' => 'A borĂ­tĂł mezĹ‘ maximum 255 karakter lehet.',
        ];
    }
}
