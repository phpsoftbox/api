<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Version;

interface ApiVersionEnumInterface
{
    public function version(): string;
}
