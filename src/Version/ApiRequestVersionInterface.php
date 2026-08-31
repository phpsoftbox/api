<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Version;

interface ApiRequestVersionInterface
{
    public function selected(): ApiVersionEnumInterface;

    public function minimumSupported(): ApiVersionEnumInterface;

    public function latestSupported(): ApiVersionEnumInterface;

    public function requestedRaw(): ?string;

    public function wasExplicitlyRequested(): bool;

    public function isGreaterThanOrEqual(ApiVersionEnumInterface $version): bool;

    public function isLowerThan(ApiVersionEnumInterface $version): bool;
}
