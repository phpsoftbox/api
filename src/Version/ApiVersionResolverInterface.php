<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Version;

use Psr\Http\Message\ServerRequestInterface;

interface ApiVersionResolverInterface
{
    public function resolve(ServerRequestInterface $request): ?string;
}
