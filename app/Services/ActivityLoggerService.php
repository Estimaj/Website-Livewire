<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLoggerService
{
    /**
     * Log an activity to the database.
     */
    public function log(string $type, ?array $additionalData = null): void
    {
        ActivityLog::create([
            'type' => $type,
            'user_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $additionalData ? json_encode($additionalData) : null,
        ]);
    }

    /**
     * Get the number of time an activity was logged.
     */
    public static function count(string $type): int
    {
        return ActivityLog::where('type', $type)->count("id");
    }
}