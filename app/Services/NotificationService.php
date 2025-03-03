<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    public function send(
        Model $notifiable,
        string $templateName,
        array $data = [],
        array $channels = null
    ): void {
        $template = NotificationTemplate::where('name', $templateName)->firstOrFail();
        
        $channels = $channels ?? $template->channels;
        $content = $this->parseTemplate($template->content, $data);
        $subject = $this->parseTemplate($template->subject, $data);

        foreach ($channels as $channel) {
            $this->sendToChannel($notifiable, $channel, $template->type, $subject, $content, $data);
        }
    }

    public function sendDirect(
        Model $notifiable,
        string $type,
        string $subject,
        string $content,
        array $data = [],
        array $channels = ['database']
    ): void {
        foreach ($channels as $channel) {
            $this->sendToChannel($notifiable, $channel, $type, $subject, $content, $data);
        }
    }

    private function sendToChannel(
        Model $notifiable,
        string $channel,
        string $type,
        string $subject,
        string $content,
        array $data
    ): void {
        match ($channel) {
            'email' => $this->sendEmail($notifiable, $subject, $content, $data),
            'sms' => $this->sendSms($notifiable, $content, $data),
            'push' => $this->sendPush($notifiable, $subject, $content, $data),
            'database' => $this->saveToDatabase($notifiable, $type, $subject, $content, $data),
            default => throw new \Exception('Invalid notification channel')
        };
    }

    private function sendEmail($notifiable, $subject, $content, $data): void
    {
        if (!$notifiable->email) {
            return;
        }

        Mail::send([], [], function ($message) use ($notifiable, $subject, $content) {
            $message->to($notifiable->email)
                ->subject($subject)
                ->html($content);
        });
    }

    private function sendSms($notifiable, $content, $data): void
    {
        if (!$notifiable->phone) {
            return;
        }

        // Implement SMS sending logic here
        // Example using a third-party service
        Http::post('sms-provider-url', [
            'to' => $notifiable->phone,
            'message' => $content
        ]);
    }

    private function sendPush($notifiable, $subject, $content, $data): void
    {
        if (!$notifiable->device_tokens) {
            return;
        }

        // Implement push notification logic here
        // Example using Firebase Cloud Messaging
        Http::withHeaders([
            'Authorization' => 'key=' . config('services.fcm.key'),
            'Content-Type' => 'application/json'
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'registration_ids' => $notifiable->device_tokens,
            'notification' => [
                'title' => $subject,
                'body' => $content
            ],
            'data' => $data
        ]);
    }

    private function saveToDatabase($notifiable, $type, $subject, $content, $data): void
    {
        Notification::create([
            'type' => $type,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id,
            'data' => array_merge($data, [
                'subject' => $subject,
                'content' => $content
            ])
        ]);
    }

    private function parseTemplate(string $template, array $data): string
    {
        return preg_replace_callback('/\{\{(.*?)\}\}/', function ($matches) use ($data) {
            $key = trim($matches[1]);
            return $data[$key] ?? '';
        }, $template);
    }
} 