<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Tests\Fixtures;

use PhpSoftBox\Api\Version\ApiVersionEnumInterface;

enum TestApiVersionEnum: string implements ApiVersionEnumInterface
{
    case VERSION_1_0_0 = '1.0.0';
    case VERSION_1_1_0 = '1.1.0';

    public function version(): string
    {
        return $this->value;
    }
}
