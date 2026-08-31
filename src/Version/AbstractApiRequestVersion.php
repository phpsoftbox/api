<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Version;

use InvalidArgumentException;

abstract readonly class AbstractApiRequestVersion implements ApiRequestVersionInterface
{
    public function __construct(
        private ApiVersionEnumInterface $selected,
        private ApiVersionEnumInterface $minimumSupported,
        private ApiVersionEnumInterface $latestSupported,
        private ?string $requestedRaw,
        private bool $explicitlyRequested,
    ) {
    }

    public function selected(): ApiVersionEnumInterface
    {
        return $this->selected;
    }

    public function minimumSupported(): ApiVersionEnumInterface
    {
        return $this->minimumSupported;
    }

    public function latestSupported(): ApiVersionEnumInterface
    {
        return $this->latestSupported;
    }

    public function requestedRaw(): ?string
    {
        return $this->requestedRaw;
    }

    public function wasExplicitlyRequested(): bool
    {
        return $this->explicitlyRequested;
    }

    public function isGreaterThanOrEqual(ApiVersionEnumInterface $version): bool
    {
        $this->assertCompatibleVersion($version);

        return ApiVersion::parse($this->selected->version())->compare(ApiVersion::parse($version->version())) >= 0;
    }

    public function isLowerThan(ApiVersionEnumInterface $version): bool
    {
        $this->assertCompatibleVersion($version);

        return ApiVersion::parse($this->selected->version())->compare(ApiVersion::parse($version->version())) < 0;
    }

    private function assertCompatibleVersion(ApiVersionEnumInterface $version): void
    {
        if ($version::class !== $this->selected::class) {
            throw new InvalidArgumentException('API feature gates must use the selected version enum.');
        }
    }
}
