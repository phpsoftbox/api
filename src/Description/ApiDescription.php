<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Description;

use PhpSoftBox\Api\Error\ApiErrorCatalog;
use PhpSoftBox\Api\Version\ApiVersionPolicyInterface;

final readonly class ApiDescription
{
    public function __construct(
        public string $id,
        public ApiErrorCatalog $errors,
        public ?ApiVersionPolicyInterface $versionPolicy = null,
    ) {
    }

    public static function create(string $id): ApiDescriptionBuilder
    {
        return new ApiDescriptionBuilder($id);
    }
}
