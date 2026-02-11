<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:125|unique:people,name',
            'gender' => 'required|integer',
            'photo' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'A név megadása kötelező.',
            'name.string' => 'A névnek szövegnek kell lennie.',
            'name.max' => 'A név nem lehet hosszabb 125 karakternél.',
            'name.unique' => 'Ezzel a névvel már létezik személy az adatbázisban.',

            'gender.required' => 'A nem megadása kötelező.',
            'gender.integer' => 'A nem értékének számnak kell lennie.',

            'photo.required' => 'A fénykép megadása kötelező.',
            'photo.string' => 'A fényképnek szöveg formátumúnak kell lennie (pl. URL vagy fájlnév).',
        ];
    }
}
