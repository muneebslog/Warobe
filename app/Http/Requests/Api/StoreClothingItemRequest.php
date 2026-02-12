<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreClothingItemRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:shirt,pant,shalwar_kameez'],
            'color' => ['required', 'string', 'max:100'],
            'formality' => ['required', 'string', 'in:casual,office,wedding,jummah,eid,interview'],
            'season' => ['required', 'string', 'in:summer,winter,all'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
