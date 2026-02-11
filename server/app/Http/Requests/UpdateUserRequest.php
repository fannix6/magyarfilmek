<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'password' => 'nullable',
            'role' => 'nullable',
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
            'name.string' => 'A névnek szövegnek kell lennie.',

            'email.email' => 'Érvényes email címet kell megadni.',

            'password.string' => 'A jelszónak szövegnek kell lennie.',

            'role.string' => 'A szerepkörnek szövegnek kell lennie.',
        ];
    }
}
