<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClothingItemRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', 'in:shirt,pant,shalwar_kameez'],
            'color' => ['sometimes', 'required', 'string', 'max:100'],
            'formality' => ['sometimes', 'required', 'string', 'in:casual,office,wedding,jummah,eid,interview'],
            'season' => ['sometimes', 'required', 'string', 'in:summer,winter,all'],
            'status' => ['sometimes', 'required', 'string', 'in:clean,worn,dry_clean,laundry'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
