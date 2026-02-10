<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$client = new Client([
    'verify' => false,          // отключает проверку SSL
]);

$domains = \App\Models\Domain::all();

foreach ($domains as $domain) {
    $start = microtime(true);

    $result = ['status' => 'UNKNOWN', 'code' => null, 'time' => null, 'error' => null];

    try {
        $response = $client->request($domain->method, $domain->domain_name, [
            'timeout'         => $domain->timeout,
            'http_errors'     => false,
            'verify'          => false,
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

    // Запись в базу
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

    // Получаем свежий лог (по id, чтобы точно свежий)
    $log = DB::table('check_logs')->find($logId);

    // Уведомление ВСЕГДА
    $user = \App\Models\User::find($domain->user_id);
    if ($user) {
    $user->notify(new \App\Notifications\DomainCheckFailed(
        $domain,
        $log,
        $user->name  // ← добавляем третий параметр
    ));
}

    // Вывод в консоль (оставляем как есть)
    $line = "{$result['status']}: {$domain->domain_name}";
    if ($result['code']) $line .= " → {$result['code']}";
    if ($result['time']) $line .= " ({$result['time']}ms)";
    if ($result['error']) $line .= " → {$result['error']}";
    echo $line . "\n";
}

echo "Проверки завершены.\n";