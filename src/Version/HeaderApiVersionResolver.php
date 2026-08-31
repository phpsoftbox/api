<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Version;

use InvalidArgumentException;
use PhpSoftBox\Api\Version\Exception\InvalidApiVersionException;
use Psr\Http\Message\ServerRequestInterface;

use function array_filter;
use function array_values;
use function count;
use function trim;

final readonly class HeaderApiVersionResolver implements ApiVersionResolverInterface
{
    public string $headerName;

    public function __construct(string $headerName = 'X-API-VERSION')
    {
        $this->headerName = trim($headerName);
        if ($this->headerName === '') {
            throw new InvalidArgumentException('API version header name cannot be empty.');
        }
    }

    public function resolve(ServerRequestInterface $request): ?string
    {
        $values = array_values(array_filter(
            $request->getHeader($this->headerName),
            static fn (string $value): bool => trim($value) !== '',
        ));

        if ($values === []) {
            return null;
        }
        if (count($values) > 1) {
            throw new InvalidApiVersionException(null, 'API version header must contain a single value.');
        }

        return trim($values[0]);
    }
}
