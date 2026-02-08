<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class invoiceRequest extends FormRequest
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
// ==================== Purchase Invoice ====================
        'supplier_id'=> 'nullballe|exists:suppliers,id',
        'invoice_number'=>  'required|string|max:255', ,
        'invoice_date' => 'required|date',
        'total'=> 'required|numeric|min:0',
        'discount'=> 'nullable|numeric|min:0',
        'net_total'=> 'required|numeric|min:0',
        'status'=>'required|in:pending,paid,partial',
// =================== Purchase Invoice Item ====================
 'purchase_invoice_id'=> 'required|exists:purchase_invoices,id',
        'product_id'=> 'required|exists:products,id',
        'quantity'  => 'required|numeric|min:0',
        'cost'    => 'required|numeric|min:0',
// ==================== Sales Invoice ====================
        'customer_id'   => 'nullable|exists:customers,id',
    
// =================== Sales Invoice Item ====================

        'sales_invoice_id'  => 'required|exists:sales_invoices,id',
        'price'    => 'required|numeric|min:0',
        ];
    }
}
