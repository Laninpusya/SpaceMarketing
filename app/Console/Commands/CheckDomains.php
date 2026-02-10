<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Domain;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;

class CheckDomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'domains:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically check domains based on user settings and intervals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Находим пользователей с активными автопроверками, где срок еще не истек
        $users = User::where('auto_check_active', true)
                     ->where('auto_check_enabled_until', '>', now())
                     ->get();

        if ($users->isEmpty()) {
            $this->info('No users with active auto-checks found.');
            return;
        }

        $client = new Client([
            'verify' => false,
        ]);

        foreach ($users as $user) {
            // Проверяем, не истек ли срок прямо сейчас (на случай задержек)
            if ($user->auto_check_enabled_until < now()) {
                $user->auto_check_active = false;
                $user->save();
                $this->info("Auto-checks disabled for user {$user->id} due to expiration.");
                continue;
            }

            // Получаем домены пользователя, которые пора проверить
            $domains = Domain::where('user_id', $user->id)
                             ->where(function ($query) {
                                 $query->whereNull('last_checked_at')
                                       ->orWhereRaw('last_checked_at + INTERVAL check_interval MINUTE < NOW()');
                             })
                             ->get();

            if ($domains->isEmpty()) {
                $this->info("No domains to check for user {$user->id}.");
                continue;
            }

            foreach ($domains as $domain) {
                $start = microtime(true);

                $result = ['status' => 'UNKNOWN', 'code' => null, 'time' => null, 'error' => null];

                try {
                    $response = $client->request($domain->method, $domain->domain_name, [
                        'timeout'     => $domain->timeout,
                        'http_errors' => false,
                        'verify'      => false,
                    ]);

                    $time_ms      = round((microtime(true) - $start) * 1000);
                    $status_code  = $response->getStatusCode();
                    $success      = $status_code >= 200 && $status_code < 400;

                    $result = [
                        'status' => $success ? 'OK' : 'DOWN',
                        'code'   => $status_code,
                        'time'   => $time_ms,
                        'error'  => $success ? null : "HTTP $status_code"
                    ];

                } catch (\Exception $e) {
                    $time_ms = round((microtime(true) - $start) * 1000);
                    $result = [
                        'status' => 'ERROR',
                        'code'   => null,
                        'time'   => $time_ms,
                        'error'  => $e->getMessage()
                    ];
                }

                // Обновляем last_checked_at
                $domain->last_checked_at = now();
                $domain->save();

                // Запись в лог
                $logId = DB::table('check_logs')->insertGetId([
                    'domain_id'        => $domain->id,
                    'checked_at'       => now(),
                    'status_code'      => $result['code'],
                    'response_time_ms' => $result['time'],
                    'success'          => $result['status'] === 'OK',
                    'error_message'    => $result['error'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                $log = DB::table('check_logs')->find($logId);

                // Уведомление ТОЛЬКО если НЕУДАЧА (поскольку в ТЗ уведомления - плюс, и чтобы не спамить)
                if ($result['status'] !== 'OK') {
                    $user->notify(new \App\Notifications\DomainCheckFailed(
                        $domain,
                        $log,
                        $user->name
                    ));
                }

                // Вывод в консоль для отладки
                $line = "{$result['status']}: {$domain->domain_name}";
                if ($result['code']) $line .= " → {$result['code']}";
                if ($result['time']) $line .= " ({$result['time']}ms)";
                if ($result['error']) $line .= " → {$result['error']}";
                $this->info($line);
            }
        }

        $this->info('Domain checks completed.');
    }
}