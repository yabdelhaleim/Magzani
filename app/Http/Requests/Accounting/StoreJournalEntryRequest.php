<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_date'              => 'required|date',
            'description'             => 'required|string|max:500',
            'reference'               => 'nullable|string|max:100',
            'post_immediately'        => 'boolean',

            'lines'                   => 'required|array|min:2',
            'lines.*.account_id'      => 'required|exists:accounts,id',
            'lines.*.debit'           => 'required|numeric|min:0',
            'lines.*.credit'          => 'required|numeric|min:0',
            'lines.*.description'     => 'nullable|string|max:255',
            'lines.*.cost_center_id'  => 'nullable|exists:cost_centers,id',
        ];
    }

    /**
     * تحقق إضافي: مجموع المدين = مجموع الدائن
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $lines = $this->input('lines', []);

            $totalDebit  = collect($lines)->sum(fn ($l) => (float)($l['debit']  ?? 0));
            $totalCredit = collect($lines)->sum(fn ($l) => (float)($l['credit'] ?? 0));

            if (abs($totalDebit - $totalCredit) > 0.01) {
                $validator->errors()->add(
                    'lines',
                    "القيد غير متوازن: مجموع المدين ({$totalDebit}) ≠ مجموع الدائن ({$totalCredit})"
                );
            }

            // كل سطر يجب أن يكون مدين أو دائن (ليس الاثنين أو صفراً)
            foreach ($lines as $i => $line) {
                $d = (float)($line['debit']  ?? 0);
                $c = (float)($line['credit'] ?? 0);

                if ($d > 0 && $c > 0) {
                    $validator->errors()->add("lines.{$i}", 'السطر لا يمكن أن يكون مديناً ودائناً في نفس الوقت.');
                }
                if ($d == 0 && $c == 0) {
                    $validator->errors()->add("lines.{$i}", 'يجب أن يحتوي السطر على قيمة مدين أو دائن.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'entry_date.required'         => 'تاريخ القيد مطلوب.',
            'description.required'        => 'وصف القيد مطلوب.',
            'lines.required'              => 'يجب إضافة سطرين على الأقل.',
            'lines.min'                   => 'يجب إضافة سطرين على الأقل.',
            'lines.*.account_id.required' => 'الحساب مطلوب في كل سطر.',
            'lines.*.account_id.exists'   => 'الحساب المحدد غير موجود.',
            'lines.*.debit.numeric'       => 'قيمة المدين يجب أن تكون رقماً.',
            'lines.*.credit.numeric'      => 'قيمة الدائن يجب أن تكون رقماً.',
        ];
    }
}
