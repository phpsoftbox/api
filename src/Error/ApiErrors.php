<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Error;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class ApiErrors
{
    /** @var list<ApiErrorCodeInterface> */
    public array $errors;

    public function __construct(ApiErrorCodeInterface ...$errors)
    {
        $this->errors = $errors;
    }
}
