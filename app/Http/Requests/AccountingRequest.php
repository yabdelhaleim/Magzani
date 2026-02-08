<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountingRequest extends FormRequest
{
    public function authorize()
    {
        return true; // لو عندك صلاحيات ممكن تتحقق هنا
    }

    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,bank,check',
            'customer_id' => 'nullable|exists:customers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'expense_date' => 'nullable|date',
            'description' => 'nullable|string|max:500',
            'reference_number' => 'nullable|string|max:100',
        ];
    }
}
