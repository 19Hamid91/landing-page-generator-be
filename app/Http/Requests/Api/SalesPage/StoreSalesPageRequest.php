<?php

namespace App\Http\Requests\Api\SalesPage;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_name'        => 'required|string|max:255',
            'product_description' => 'required|string',
            'target_audience'     => 'required|string|max:255',
            'price'               => 'nullable|numeric|min:0',
            'features'            => 'nullable|array',
            'features.*'          => 'string',
            'usp'                 => 'nullable|array',
            'usp.*'               => 'string',
            'template_name'       => 'nullable|string|max:100',
            'images'              => 'nullable|array',
            'images.*'            => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'language'            => 'nullable|string|in:id,en',
            'currency'            => 'nullable|string|in:IDR,USD',
        ];
    }
}
