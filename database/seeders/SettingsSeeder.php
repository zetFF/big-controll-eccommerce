<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // System Settings
            [
                'key' => 'site_name',
                'value' => config('app.name'),
                'group' => 'system',
                'type' => 'text',
                'description' => 'The name of your application',
                'is_public' => true
            ],
            [
                'key' => 'site_description',
                'value' => 'A Laravel application',
                'group' => 'system',
                'type' => 'textarea',
                'description' => 'A short description of your application',
                'is_public' => true
            ],
            [
                'key' => 'maintenance_mode',
                'value' => false,
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Put the application into maintenance mode',
                'is_public' => false
            ],

            // Email Settings
            [
                'key' => 'mail_from_name',
                'value' => config('mail.from.name'),
                'group' => 'mail',
                'type' => 'text',
                'description' => 'The name that emails are sent from',
                'is_public' => false
            ],
            [
                'key' => 'mail_from_address',
                'value' => config('mail.from.address'),
                'group' => 'mail',
                'type' => 'email',
                'description' => 'The email address that emails are sent from',
                'is_public' => false
            ],

            // API Settings
            [
                'key' => 'api_rate_limit',
                'value' => 60,
                'group' => 'api',
                'type' => 'number',
                'description' => 'Number of API requests allowed per minute',
                'is_public' => true
            ],
            [
                'key' => 'api_token_expiry',
                'value' => 60,
                'group' => 'api',
                'type' => 'number',
                'description' => 'Number of days before API tokens expire',
                'is_public' => true
            ],

            // Backup Settings
            [
                'key' => 'backup_enabled',
                'value' => true,
                'group' => 'backup',
                'type' => 'boolean',
                'description' => 'Enable automatic backups',
                'is_public' => false
            ],
            [
                'key' => 'backup_frequency',
                'value' => 'daily',
                'group' => 'backup',
                'type' => 'select',
                'description' => 'How often to run backups',
                'options' => [
                    'hourly' => 'Every Hour',
                    'daily' => 'Every Day',
                    'weekly' => 'Every Week',
                    'monthly' => 'Every Month'
                ],
                'is_public' => false
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
} 