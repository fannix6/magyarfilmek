<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
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
        'movieid' => 'required|integer|exists:movies,id',

        // 'movieid' => [
            
        //     'required',
        //     'integer',
        //     Rule::unique('tasks')->where(function ($query) {
        //         return $query
        //             ->where('personid', $this->personid)
        //             ->where('roleid', $this->roleid);
        //     }),
        // ],
        'personid' => 'required|integer',
        'roleid'   => 'required|integer',
    ];
}
}
