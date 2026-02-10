<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Notifications\Notification;
use App\Models\Domain;
use Illuminate\Support\Carbon;

class DomainCheckFailed extends Notification
{
    use Queueable;

    protected $domain;
    protected $log;
    protected $userName;

    public function __construct(Domain $domain, $log, string $userName)
    {
        $this->domain   = $domain;
        $this->log      = $log;
        $this->userName = $userName;
    }

    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        $emoji = match ($this->log->success) {
            true  => '✅',
            false => $this->log->status_code ? '⚠️' : '🚨',
            default => '❓',
        };

        $status = $this->log->success ? 'OK' : 
            ($this->log->status_code ? "DOWN ({$this->log->status_code})" : 'ERROR');

        $text = "{$emoji} {$status} — {$this->domain->domain_name}\n\n";
        $text .= "Пользователь: {$this->userName} (ID {$this->domain->user_id})\n";
        $text .= "Время: " . \Carbon\Carbon::parse($this->log->checked_at)->format('Y-m-d H:i:s') . "\n";
        
        if ($this->log->status_code) {
            $text .= "Код ответа: {$this->log->status_code}\n";
        }
        
        $text .= "Время ответа: {$this->log->response_time_ms} мс\n";
        
        if ($this->log->error_message) {
            $text .= "Ошибка: {$this->log->error_message}\n";
        }

        return TelegramMessage::create($text)
            ->to(env('TELEGRAM_CHANNEL_ID'))
            ->options(['verify' => false]);
    }
}