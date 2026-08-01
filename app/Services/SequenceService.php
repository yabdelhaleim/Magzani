<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SequenceService
{
    /**
     * توليد رقم فريد ومتزامن بشكل آمن باستخدام lockForUpdate على جدول invoice_sequences
     *
     * @param string $type نوع السلسلة (مثال: 'sales', 'purchase', 'manufacturing', 'warehouse_inbound', 'warehouse_outbound')
     * @param string $prefix البادئة (مثال: 'S', 'P', 'MO-', 'IN-', 'OUT-')
     * @param int $padding طول الجزء الرقمي للرقم المتسلسل
     * @return string الرقم الفريد المولد
     */
    public function generateNext(string $type, string $prefix, int $padding = 5): string
    {
        return DB::transaction(function() use ($type, $prefix, $padding) {
            $year = date('Y');
            
            // 🔒 قفل الـ sequence على مستوى الصف لمنع التكرار تماماً
            $sequence = DB::table('invoice_sequences')
                ->where('type', $type)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();
            
            if (!$sequence) {
                DB::table('invoice_sequences')->insert([
                    'type'        => $type,
                    'year'        => $year,
                    'last_number' => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $number = 1;
            } else {
                $number = $sequence->last_number + 1;
                DB::table('invoice_sequences')
                    ->where('id', $sequence->id)
                    ->update([
                        'last_number' => $number,
                        'updated_at'  => now()
                    ]);
            }
            
            // إذا كانت البادئة تنتهي بـ شرطة، نولد بالتنسيق: MO-2026-0001
            if (str_ends_with($prefix, '-')) {
                $cleanPrefix = rtrim($prefix, '-');
                return sprintf('%s-%s-%0' . $padding . 'd', $cleanPrefix, $year, $number);
            }
            
            // التنسيق الافتراضي المدمج: S202600001
            return sprintf('%s%s%0' . $padding . 'd', $prefix, $year, $number);
        });
    }
}
