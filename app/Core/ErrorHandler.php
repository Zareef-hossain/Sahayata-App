<?php

declare(strict_types=1);

namespace App\Core;

use ErrorException;
use Throwable;

/**
 * Turns PHP warnings into exceptions and shows a clean error page (or JSON)
 * instead of a blank screen. Errors are written to storage/logs/app.log.
 */
final class ErrorHandler
{
    private static bool $debug = false;

    public static function register(bool $debug): void
    {
        self::$debug = $debug;
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        ini_set('error_log', APP_ROOT . '/storage/logs/app.log');

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler([self::class, 'handle']);
    }

    public static function handle(Throwable $error): void
    {
        $status = $error instanceof HttpException ? $error->status : 500;

        if ($status >= 500) {
            error_log((string) $error);
        }

        $message = match (true) {
            $error instanceof HttpException && $error->getMessage() !== '' => $error->getMessage(),
            $status === 404 => 'Page not found.',
            $status === 403 => 'You do not have permission to open this page.',
            default => 'Something went wrong. Please try again.',
        };
        $details = (self::$debug && $status >= 500) ? $error->getMessage() . ' in ' . $error->getFile() . ':' . $error->getLine() : null;

        if (!headers_sent()) {
            http_response_code($status);
        }

        if ((new Request())->wantsJson()) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode(array_filter([
                'success' => false,
                'message' => $message,
                'details' => $details,
            ], static fn ($value) => $value !== null));
            return;
        }

        echo View::render('errors/error', [
            'title'   => (string) $status,
            'status'  => $status,
            'message' => $message,
            'details' => $details,
        ]);
    }
}
