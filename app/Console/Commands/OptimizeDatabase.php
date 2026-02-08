<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OptimizeDatabase extends Command
{
    protected $signature = 'db:optimize';
    protected $description = 'تنضيف وتحسين جداول الـ Database';

    public function handle()
    {
        $this->info('🔄 جاري تحسين الـ Database...');
        
        // جيب كل أسماء الجداول
        $tables = DB::select('SHOW TABLES');
        
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            
            $this->info("⚙️  بينضف: {$tableName}");
            
            // تنضيف وتحسين الجدول
            DB::statement("OPTIMIZE TABLE {$tableName}");
        }
        
        $this->info('✅ تم تحسين الـ Database بنجاح!');
        
        return 0;
    }
}