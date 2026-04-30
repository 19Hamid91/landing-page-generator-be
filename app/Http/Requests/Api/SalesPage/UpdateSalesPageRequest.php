<?php

namespace App\Http\Requests\Api\SalesPage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('sales_page'));
    }

    public function rules(): array
    {
        return [
            'product_name'        => 'sometimes|required|string|max:255',
            'product_description' => 'sometimes|required|string',
            'target_audience'     => 'sometimes|required|string|max:255',
            'price'               => 'nullable|numeric|min:0',
            'features'            => 'nullable|array',
            'features.*'          => 'string',
            'usp'                 => 'nullable|array',
            'usp.*'               => 'string',
            'template_name'       => 'nullable|string|max:100',
            'images'              => 'nullable|array',
            'images.*'            => 'nullable', // Can be file or string (URL)
            'language'            => 'nullable|string|in:id,en',
            'currency'            => 'nullable|string|in:IDR,USD',
            'ai_output'           => 'nullable', // Will be JSON string in FormData
            'regenerate'          => 'boolean',
        ];
    }
}
