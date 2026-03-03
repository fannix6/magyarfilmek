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
                    ->ignore($this->route('id')),
            ],

            'personid' => 'required|integer|exists:people,id',
            'roleid'   => 'required|integer|exists:roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'movieid.required' => 'A film kivĂˇlasztĂˇsa kĂ¶telezĹ‘.',
            'movieid.integer'  => 'A film azonosĂ­tĂłnak szĂˇmnak kell lennie.',
            'movieid.exists'   => 'A kivĂˇlasztott film nem talĂˇlhatĂł az adatbĂˇzisban.',
            'movieid.unique'   => 'Ez a feladat mĂˇr lĂ©tezik ugyanazzal a szemĂ©llyel Ă©s szerepkĂ¶rrel.',

            'personid.required' => 'A szemĂ©ly kivĂˇlasztĂˇsa kĂ¶telezĹ‘.',
            'personid.integer'  => 'A szemĂ©ly azonosĂ­tĂłnak szĂˇmnak kell lennie.',
            'personid.exists'   => 'A kivĂˇlasztott szemĂ©ly nem talĂˇlhatĂł az adatbĂˇzisban.',

            'roleid.required' => 'A szerepkĂ¶r kivĂˇlasztĂˇsa kĂ¶telezĹ‘.',
            'roleid.integer'  => 'A szerepkĂ¶r azonosĂ­tĂłnak szĂˇmnak kell lennie.',
            'roleid.exists'   => 'A kivĂˇlasztott szerepkĂ¶r nem talĂˇlhatĂł az adatbĂˇzisban.',
        ];
    }
}
