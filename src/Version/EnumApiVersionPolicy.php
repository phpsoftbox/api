<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Version;

final readonly class EnumApiVersionPolicy implements ApiVersionPolicyInterface
{
    /**
     * @param list<ApiVersionEnumInterface> $supportedVersions
     */
    public function __construct(
        private array $supportedVersions,
        private ApiVersionEnumInterface $defaultVersion,
    ) {
        new PublishedApiVersionSet($this);
    }

    /** @return list<ApiVersionEnumInterface> */
    public function supportedVersions(): array
    {
        return $this->supportedVersions;
    }

    public function defaultVersion(): ApiVersionEnumInterface
    {
        return $this->defaultVersion;
    }
}
