<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'link' => 'required|url',
            'image' => $this->isMethod('POST') 
                ? 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120'  // 5MB max
                : 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'category_id' => 'required|exists:categories,id'
        ];
    }
} 