<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $formatEmail = 'regex:/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
        return [
            'to' => 'required|array|min:1',
            'to.*' => ['required', 'email', $formatEmail, 'max:320'],
            'cc' => 'sometimes|array|nullable',
            'cc.*' => ['email', $formatEmail, 'max:320'],
            'bcc' => 'sometimes|array|nullable',
            'bcc.*' => ['email', $formatEmail, 'max:320'],
            'from' => 'required|email',
            'subject' => 'sometimes|string|max:200|nullable',
            'body' => 'sometimes|string|nullable',
            "attachments" => 'sometimes|array',
            'attachments.*' => 'required|file|max:25600'
        ];
    }
}
