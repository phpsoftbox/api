<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Integration\Application;

use PhpSoftBox\Api\Error\CommonApiErrorEnum;
use PhpSoftBox\Api\Version\ApiVersionPolicyInterface;
use PhpSoftBox\Api\Version\ApiVersionResponseHeaders;
use PhpSoftBox\Api\Version\Exception\InvalidApiVersionException;
use PhpSoftBox\Api\Version\Exception\UnsupportedApiVersionException;
use PhpSoftBox\Api\Version\PublishedApiVersionSet;
use PhpSoftBox\Application\ErrorHandler\ExceptionMapperInterface;
use PhpSoftBox\Application\Exception\CodedHttpException;
use Throwable;

final readonly class ApiVersionExceptionMapper implements ExceptionMapperInterface
{
    private PublishedApiVersionSet $versions;

    public function __construct(
        ApiVersionPolicyInterface $policy,
        private ApiVersionResponseHeaders $headers = new ApiVersionResponseHeaders(),
    ) {
        $this->versions = new PublishedApiVersionSet($policy);
    }

    public function map(Throwable $exception): ?Throwable
    {
        if ($exception instanceof InvalidApiVersionException) {
            return $this->mapInvalid($exception);
        }
        if ($exception instanceof UnsupportedApiVersionException) {
            return $this->mapUnsupported($exception);
        }

        return null;
    }

    private function mapInvalid(InvalidApiVersionException $exception): CodedHttpException
    {
        $definition = CommonApiErrorEnum::INVALID_API_VERSION->definition();

        return new CodedHttpException(
            statusCode: $definition->status,
            errorCode: $definition->code,
            displayMessage: $definition->description,
            headers: $this->errorHeaders(),
            title: $definition->title,
            debugMessage: $exception->getMessage(),
            previous: $exception,
        );
    }

    private function mapUnsupported(UnsupportedApiVersionException $exception): CodedHttpException
    {
        $definition = CommonApiErrorEnum::UNSUPPORTED_API_VERSION->definition();

        return new CodedHttpException(
            statusCode: $definition->status,
            errorCode: $definition->code,
            displayMessage: $definition->description,
            details: ['supported_versions' => $exception->supportedVersions],
            headers: $this->errorHeaders(),
            title: $definition->title,
            debugMessage: $exception->getMessage(),
            previous: $exception,
        );
    }

    /** @return array<string, string> */
    private function errorHeaders(): array
    {
        return [
            $this->headers->minimum => $this->versions->minimum()->version(),
            $this->headers->latest  => $this->versions->latest()->version(),
            'Vary'                  => $this->headers->request,
        ];
    }
}
