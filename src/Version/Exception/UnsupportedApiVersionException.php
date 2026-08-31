<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Version\Exception;

use RuntimeException;

final class UnsupportedApiVersionException extends RuntimeException
{
    /**
     * @param list<string> $supportedVersions
     */
    public function __construct(
        public readonly string $requestedVersion,
        public readonly array $supportedVersions,
    ) {
        parent::__construct('The supplied API version has not been published by this API.');
    }
}
