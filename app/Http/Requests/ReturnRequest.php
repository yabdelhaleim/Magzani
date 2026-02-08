<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReturnRequest extends FormRequest
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
// ==================== Purchase Return ====================
        'purchase_invoice_id'=>'required|exists:purchase_invoices,id',
        'return_date'   => 'required|date', ,
        'reason'    => 'nullable|string|max:500',   
        'total'   => 'required|numeric|min:0', 
// ==================== Items ====================
         'purchase_return_id'   =>'required|exists:purchase_returns,id',
        'product_id'=>'required|exists:products,id',        ,
        'quantity'=>'required|numeric|min:1',
        'cost'  =>'required|numeric|min:0',
// ===================== Sales Return ====================
         'sales_invoice_id' =>'required|exists:sales_invoices,id',
        'customer_id'   =>'required|exists:customers,id',
        'warehouse_id'=>'required|exists:warehouses,id',
        'return_number' => 'required|string|max:100|unique:sales_returns,return_number',,
        'status'=>'required|string|in:pending,completed,canceled',
        'notes' => 'nullable|string|max:500',
//===================== Items ====================  
         'sales_return_id'  =>'required|exists:sales_returns,id',
        'price' =>'required|numeric|min:0',
        ];
    }
}
