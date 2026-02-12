<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SuggestOutfitRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<int, string>>
     */
    public function rules(): array
    {
        return [
            'event_type' => ['required', 'string', 'in:casual,office,wedding,jummah,eid,interview'],
        ];
    }
}
