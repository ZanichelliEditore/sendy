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
        return [
            'to' => 'required|array|min:1',
            'to.*' => 'required|email',
            'cc' => 'sometimes|array|nullable',
            'cc.*' => 'email',
            'bcc' => 'sometimes|array|nullable',
            'bcc.*' => 'email',
            'from' => 'required|email',
            'subject' => 'sometimes|string|max:100|nullable',
            'body' => 'sometimes|string|nullable',
            "attachments" => 'sometimes|array',
            'attachments.*' => 'required|file|max:25600'
        ];
    }
}
