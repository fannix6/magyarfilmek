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
            'name' => 'required|string|max:50|unique:people,name',
            'gender' => 'nullable|integer|in:0,1',
            'photo' => 'nullable|string|max:125',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'A nĂ©v megadĂˇsa kĂ¶telezĹ‘.',
            'name.string' => 'A nĂ©vnek szĂ¶vegnek kell lennie.',
            'name.max' => 'A nĂ©v nem lehet hosszabb 50 karakternĂ©l.',
            'name.unique' => 'Ezzel a nĂ©vvel mĂˇr lĂ©tezik szemĂ©ly az adatbĂˇzisban.',

            'gender.integer' => 'A nem Ă©rtĂ©kĂ©nek szĂˇmnak kell lennie.',
            'gender.in' => 'A nem erteke csak 0 (no) vagy 1 (ferfi) lehet.',
            'photo.string' => 'A fĂ©nykĂ©pnek szĂ¶veg formĂˇtumĂşnak kell lennie (pl. URL vagy fĂˇjlnĂ©v).',
        ];
    }
}
