<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WearOutfitRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<int, string>>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'shirt_id' => [
                'nullable',
                'integer',
                Rule::exists('clothing_items', 'id')->where('user_id', $user?->id),
            ],
            'pant_id' => [
                'nullable',
                'integer',
                Rule::exists('clothing_items', 'id')->where('user_id', $user?->id),
            ],
            'shalwar_kameez_id' => [
                'nullable',
                'integer',
                Rule::exists('clothing_items', 'id')->where('user_id', $user?->id),
            ],
            'event_type' => ['required', 'string', 'in:casual,office,wedding,jummah,eid,interview'],
        ];
    }
}
