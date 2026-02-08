<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'name'=> 'required|string|max:255',
        'code' => 'required|string|unique:products,code',
        'sku'=> 'nullable|string|max:100|unique:products,sku',
        'category_id'=>'nullable|exists:categories,id',
        'cost_price'=>'required|numeric|min:0',
        'sale_price'=>'required|numeric|min:0',
        'is_active'=> 'required|boolean',
        ];
    }
}
