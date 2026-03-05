<?php

namespace Webkul\KissDataFeed\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CredentialRequest extends FormRequest
{
    public function rules()
    {
        $rules = [
            'api_url'       => 'required|url',
            'client_id'     => 'required|string|max:100',
            'client_secret' => 'required|string',
        ];

        if ($this->isMethod('PUT')) {
            $rules['client_secret'] = 'nullable|string';
        }

        return $rules;
    }
}
