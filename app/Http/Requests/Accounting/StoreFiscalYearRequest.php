<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreFiscalYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:100|unique:fiscal_years,name',
            'start_date' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'اسم السنة المالية مطلوب.',
            'name.unique'          => 'هذه السنة المالية موجودة مسبقاً.',
            'start_date.required'  => 'تاريخ بداية السنة المالية مطلوب.',
            'start_date.date'      => 'تاريخ البداية غير صحيح.',
        ];
    }
}
