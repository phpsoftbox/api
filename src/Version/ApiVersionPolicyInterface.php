<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Version;

interface ApiVersionPolicyInterface
{
    /** @return list<ApiVersionEnumInterface> */
    public function supportedVersions(): array;

    public function defaultVersion(): ApiVersionEnumInterface;
}
