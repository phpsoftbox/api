<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Version\Exception;

use RuntimeException;
use Throwable;

final class InvalidApiVersionException extends RuntimeException
{
    public function __construct(
        public readonly ?string $requestedValue,
        string $message = 'The supplied API version has an invalid format.',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
