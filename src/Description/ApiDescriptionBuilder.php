<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Description;

use InvalidArgumentException;
use PhpSoftBox\Api\Error\ApiErrorCatalog;
use PhpSoftBox\Api\Error\ApiErrorCodeInterface;
use PhpSoftBox\Api\Error\ApiErrorDefinition;
use PhpSoftBox\Api\Version\ApiVersionPolicyInterface;

use function trim;

final class ApiDescriptionBuilder
{
    private readonly string $id;
    private readonly ApiErrorCatalog $errors;
    private ?ApiVersionPolicyInterface $versionPolicy = null;

    public function __construct(string $id)
    {
        $this->id = $id;

        if (trim($this->id) === '') {
            throw new InvalidArgumentException('API description id cannot be empty.');
        }

        $this->errors = new ApiErrorCatalog();
    }

    public function withVersionPolicy(ApiVersionPolicyInterface $policy): self
    {
        $this->versionPolicy = $policy;

        return $this;
    }

    /**
     * @param iterable<ApiErrorDefinition|ApiErrorCodeInterface> $errors
     */
    public function withCommonErrors(iterable $errors): self
    {
        $this->errors->registerAll($errors);

        return $this;
    }

    /**
     * @param iterable<ApiErrorDefinition|ApiErrorCodeInterface> $errors
     */
    public function withErrors(iterable $errors): self
    {
        $this->errors->registerAll($errors);

        return $this;
    }

    public function build(): ApiDescription
    {
        $errors = clone $this->errors;

        return new ApiDescription(trim($this->id), $errors->lock(), $this->versionPolicy);
    }
}
