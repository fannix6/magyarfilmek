<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:125',
                Rule::unique('people', 'name')->ignore($this->route('person')),
            ],
            'gender' => 'sometimes|required|integer',
            'photo' => 'sometimes|required|string',
        ];
    }
}
