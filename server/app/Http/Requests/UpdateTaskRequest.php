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
}
