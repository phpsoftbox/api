<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Error;

interface ApiErrorCodeInterface
{
    public function code(): string;

    public function definition(): ApiErrorDefinition;
}
