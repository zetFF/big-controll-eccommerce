<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityService
{
    public function log(
        string $type,
        string $action,
        string $description = null,
        Model $subject = null,
        array $properties = []
    ): Activity {
        return Activity::create([
            'user_id' => Auth::id(),
            'type' => $type,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent()
        ]);
    }

    public function logAuth(string $action, string $description = null): Activity
    {
        return $this->log('auth', $action, $description);
    }

    public function logUser(string $action, Model $user, string $description = null): Activity
    {
        return $this->log('user', $action, $description, $user);
    }

    public function logSystem(string $action, string $description = null, array $properties = []): Activity
    {
        return $this->log('system', $action, $description, null, $properties);
    }

    public function logBackup(string $action, Model $backup, string $description = null): Activity
    {
        return $this->log('backup', $action, $description, $backup);
    }

    public function logSettings(string $action, array $changes): Activity
    {
        return $this->log('settings', $action, 'Settings were updated', null, $changes);
    }
} 