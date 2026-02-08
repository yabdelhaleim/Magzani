<?php

namespace App\Listeners;

use Illuminate\Support\Facades\DB;

class LogActivityListener
{
    /**
     * مستمع عام لتسجيل كل الأنشطة في قاعدة البيانات
     */
    public function handle($event): void
    {
        try {
            $eventClass = get_class($event);
            $eventName = class_basename($eventClass);

            DB::table('activity_logs')->insert([
                'event_type' => $eventName,
                'event_data' => json_encode($this->extractEventData($event)),
                'user_id' => auth()->id(),
                'user_name' => auth()->user()?->name ?? 'النظام',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to log activity', [
                'event' => get_class($event),
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function extractEventData($event): array
    {
        $data = [];
        
        foreach (get_object_vars($event) as $key => $value) {
            if (!in_array($key, ['socket', 'broadcastQueue'])) {
                $data[$key] = $this->serializeValue($value);
            }
        }

        return $data;
    }

    private function serializeValue($value)
    {
        if (is_object($value) && method_exists($value, 'getKey')) {
            return [
                'type' => get_class($value),
                'id' => $value->getKey(),
            ];
        }

        return $value;
    }
}
