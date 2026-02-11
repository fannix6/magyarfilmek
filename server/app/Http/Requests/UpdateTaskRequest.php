<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'movieid' => [
                'required',
                'integer',
                'exists:movies,id',
                Rule::unique('tasks')
                    ->where(fn ($query) =>
                        $query->where('personid', $this->personid)
                              ->where('roleid', $this->roleid)
                    )
                    ->ignore($this->route('task')),
            ],

            'personid' => 'required|integer|exists:people,id',
            'roleid'   => 'required|integer|exists:roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'movieid.required' => 'A film kiválasztása kötelező.',
            'movieid.integer'  => 'A film azonosítónak számnak kell lennie.',
            'movieid.exists'   => 'A kiválasztott film nem található az adatbázisban.',
            'movieid.unique'   => 'Ez a feladat már létezik ugyanazzal a személlyel és szerepkörrel.',

            'personid.required' => 'A személy kiválasztása kötelező.',
            'personid.integer'  => 'A személy azonosítónak számnak kell lennie.',
            'personid.exists'   => 'A kiválasztott személy nem található az adatbázisban.',

            'roleid.required' => 'A szerepkör kiválasztása kötelező.',
            'roleid.integer'  => 'A szerepkör azonosítónak számnak kell lennie.',
            'roleid.exists'   => 'A kiválasztott szerepkör nem található az adatbázisban.',
        ];
    }
}
