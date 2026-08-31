<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Error;

use InvalidArgumentException;

final class ApiErrorCatalog
{
    /** @var array<string, ApiErrorDefinition> */
    private array $definitions = [];

    private bool $locked = false;

    /**
     * @param iterable<ApiErrorDefinition|ApiErrorCodeInterface> $definitions
     */
    public function __construct(iterable $definitions = [])
    {
        $this->registerAll($definitions);
    }

    public function register(ApiErrorDefinition|ApiErrorCodeInterface $error): self
    {
        if ($this->locked) {
            throw new InvalidArgumentException('The API error catalog is immutable after the description is built.');
        }

        $definition = $error instanceof ApiErrorCodeInterface ? $error->definition() : $error;
        $existing   = $this->definitions[$definition->code] ?? null;

        if ($existing !== null && !$existing->equals($definition)) {
            throw new InvalidArgumentException('Conflicting API error definition: ' . $definition->code);
        }

        $this->definitions[$definition->code] = $definition;

        return $this;
    }

    public function lock(): self
    {
        $this->locked = true;

        return $this;
    }

    /**
     * @param iterable<ApiErrorDefinition|ApiErrorCodeInterface> $definitions
     */
    public function registerAll(iterable $definitions): self
    {
        foreach ($definitions as $definition) {
            $this->register($definition);
        }

        return $this;
    }

    public function has(string|ApiErrorCodeInterface $code): bool
    {
        $value = $code instanceof ApiErrorCodeInterface ? $code->code() : $code;

        return isset($this->definitions[$value]);
    }

    public function get(string|ApiErrorCodeInterface $code): ?ApiErrorDefinition
    {
        $value = $code instanceof ApiErrorCodeInterface ? $code->code() : $code;

        return $this->definitions[$value] ?? null;
    }

    public function require(string|ApiErrorCodeInterface $code): ApiErrorDefinition
    {
        $value      = $code instanceof ApiErrorCodeInterface ? $code->code() : $code;
        $definition = $this->definitions[$value] ?? null;
        if ($definition === null) {
            throw new InvalidArgumentException('Unknown API error code: ' . $value);
        }

        return $definition;
    }

    /**
     * @param iterable<string|ApiErrorCodeInterface> $codes
     */
    public function requireAll(iterable $codes): void
    {
        foreach ($codes as $code) {
            $this->require($code);
        }
    }

    /** @return array<string, ApiErrorDefinition> */
    public function all(): array
    {
        return $this->definitions;
    }
}
