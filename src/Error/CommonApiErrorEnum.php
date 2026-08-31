<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Error;

enum CommonApiErrorEnum: string implements ApiErrorCodeInterface
{
    case VALIDATION_FAILED       = 'validation_failed';
    case UNAUTHENTICATED         = 'unauthenticated';
    case FORBIDDEN               = 'forbidden';
    case NOT_FOUND               = 'not_found';
    case METHOD_NOT_ALLOWED      = 'method_not_allowed';
    case RATE_LIMIT_EXCEEDED     = 'rate_limit_exceeded';
    case INTERNAL_ERROR          = 'internal_error';
    case INVALID_API_VERSION     = 'invalid_api_version';
    case UNSUPPORTED_API_VERSION = 'unsupported_api_version';

    public function code(): string
    {
        return $this->value;
    }

    public function definition(): ApiErrorDefinition
    {
        return match ($this) {
            self::VALIDATION_FAILED => new ApiErrorDefinition(
                $this->value,
                422,
                'Validation failed',
                'The request payload did not pass validation.',
            ),
            self::UNAUTHENTICATED => new ApiErrorDefinition(
                $this->value,
                401,
                'Unauthenticated',
                'Valid authentication credentials are required.',
            ),
            self::FORBIDDEN => new ApiErrorDefinition(
                $this->value,
                403,
                'Forbidden',
                'The authenticated principal is not allowed to perform this operation.',
            ),
            self::NOT_FOUND => new ApiErrorDefinition(
                $this->value,
                404,
                'Not found',
                'The requested resource was not found.',
            ),
            self::METHOD_NOT_ALLOWED => new ApiErrorDefinition(
                $this->value,
                405,
                'Method not allowed',
                'The HTTP method is not allowed for this endpoint.',
            ),
            self::RATE_LIMIT_EXCEEDED => new ApiErrorDefinition(
                $this->value,
                429,
                'Rate limit exceeded',
                'The client has exceeded the configured request rate.',
            ),
            self::INTERNAL_ERROR => new ApiErrorDefinition(
                $this->value,
                500,
                'Internal server error',
                'The server could not complete the request.',
            ),
            self::INVALID_API_VERSION => new ApiErrorDefinition(
                $this->value,
                400,
                'Invalid API version',
                'The supplied API version has an invalid format.',
            ),
            self::UNSUPPORTED_API_VERSION => new ApiErrorDefinition(
                $this->value,
                406,
                'Unsupported API version',
                'The supplied API version has not been published by this API.',
            ),
        };
    }
}
