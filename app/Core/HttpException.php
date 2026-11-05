<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Throw this to stop and show an error page / JSON error,
 * e.g. throw new HttpException(403, 'You cannot open this page.');
 */
final class HttpException extends RuntimeException
{
    public function __construct(public readonly int $status, string $message = '')
    {
        parent::__construct($message, $status);
    }
}
