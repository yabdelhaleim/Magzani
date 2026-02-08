<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sales_invoice_id' => ['required', 'exists:sales_invoices,id'],
            'total_amount'     => ['required', 'numeric', 'min:0.01'],
            'reason'           => ['required', 'string', 'max:500'],
            'images.*'         => ['nullable', 'image', 'max:2048'],
        ];
    }
}
