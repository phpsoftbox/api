<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Version;

interface ApiRequestVersionFactoryInterface
{
    public function create(?string $requestedVersion): ApiRequestVersionInterface;
}
