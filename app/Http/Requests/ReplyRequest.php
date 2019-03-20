<?php

namespace App\Http\Requests;

class ReplyRequest extends Request
{
    public function rules()
    {
       return [
           'content' => 'min:2|required',
       ];
    }

    public function messages()
    {
        return [
            // Validation messages
        ];
    }
}
