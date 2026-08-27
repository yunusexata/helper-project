<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AppLog
{
    /**
     * Menyimpan Trace ID yang unik selama satu siklus request/job
     */
    private static ?string $traceId = null;

    /**
     * Mendapatkan atau membuat Trace ID
     */
    private static function getTraceId(): string
    {
        if (! self::$traceId) {
            self::$traceId = (string) Str::uuid();
        }

        return self::$traceId;
    }

    /**
     * Membangun standar struktur context array (The 5 Ws + Extra Debugging)
     */
    /**
     * Membangun standar struktur context array (The 5 Ws + Advanced Telemetry)
     */
    private static function buildContext(string $event, array $target, array $metadata, ?Throwable $exception = null): array
    {
        $context = [
            'event' => $event,
            'actor_id' => auth()->id() ?? 'system_queue',
            'target_id' => $target,
            'metadata' => $metadata,

            // --- TELEMETRY INFRASTRUKTUR ---
            'sys_metrics' => [
                'trace_id' => self::getTraceId(),
                'pid' => getmypid(), // Process ID Worker
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2).' MB',
                'peak_memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2).' MB',
                'context_type' => app()->runningInConsole() ? 'CLI/Queue' : 'HTTP',
            ],
        ];

        // Hitung waktu eksekusi sejak Laravel boot (dalam detik)
        if (defined('LARAVEL_START')) {
            $context['sys_metrics']['execution_time'] = round(microtime(true) - LARAVEL_START, 3).' s';
        }

        // Cek beban CPU Server (Khusus Linux/VPS)
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $context['sys_metrics']['cpu_load_1m'] = $load[0] ?? 'N/A';
        }

        // Tangkap URL (jika dari Web) atau Command CLI (jika dari Queue)
        if (! app()->runningInConsole()) {
            $context['sys_metrics']['ip_address'] = request()->ip();
            $context['sys_metrics']['url'] = request()->fullUrl();
        } else {
            // Menangkap argumen terminal (contoh: "artisan queue:work")
            $context['sys_metrics']['cli_command'] = isset($_SERVER['argv']) ? implode(' ', $_SERVER['argv']) : 'N/A';
        }

        // Jika ada error, format stack trace-nya agar rapi
        if ($exception) {
            $context['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
            ];
        }

        return $context;
    }

    /**
     * Menyelesaikan instance Logger (Mendukung Multi-Channel / Stack)
     */
    private static function resolveLogger(array|string|null $channels)
    {
        if (is_array($channels)) {
            return Log::stack($channels);
        }

        if (is_string($channels)) {
            return Log::channel($channels);
        }

        return Log::getFacadeRoot(); // Default laravel.log
    }

    /**
     * Helper untuk Log::info
     */
    public static function info(string $message, string $event, array $target = [], array $metadata = [], array|string|null $channels = null): void
    {
        self::resolveLogger($channels)->info($message, self::buildContext($event, $target, $metadata));
    }

    /**
     * Helper untuk Log::error
     */
    public static function error(string $message, string $event, array $target = [], array $metadata = [], ?Throwable $exception = null, array|string|null $channels = null): void
    {
        self::resolveLogger($channels)->error($message, self::buildContext($event, $target, $metadata, $exception));
    }

    /**
     * Helper untuk Log::warning
     */
    public static function warning(string $message, string $event, array $target = [], array $metadata = [], array|string|null $channels = null): void
    {
        self::resolveLogger($channels)->warning($message, self::buildContext($event, $target, $metadata));
    }
}
