<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Enums\ActivityType;

class ActivityLoggerService
{
    private array $properties = [];

    /**
     * Properties to be logged with the activity.
     */
    public function withProperties(array $properties): self
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * Log an activity to the database.
     */
    public function log(ActivityType $type): void
    {
        ActivityLog::create([
            'type' => $type,
            'user_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => json_encode($this->properties),
        ]);
    }

    /**
     * Get the number of time an activity was logged.
     */
    public static function count(ActivityType $type): int
    {
        return ActivityLog::where('type', $type)->count();
    }
}
