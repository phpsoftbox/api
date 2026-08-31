<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Description;

interface ApiDescriptionProviderInterface
{
    public function describe(ApiDescriptionBuilder $description): void;
}
