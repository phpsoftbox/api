<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Version;

use InvalidArgumentException;

use function trim;

final readonly class ApiVersionResponseHeaders
{
    public string $selected;
    public string $minimum;
    public string $latest;
    public string $request;

    public function __construct(
        string $selected = 'X-API-VERSION',
        string $minimum = 'X-API-VERSION-MIN',
        string $latest = 'X-API-VERSION-LATEST',
        ?string $request = null,
    ) {
        $this->selected = trim($selected);
        $this->minimum  = trim($minimum);
        $this->latest   = trim($latest);
        $this->request  = trim($request ?? $selected);

        if ($this->selected === '' || $this->minimum === '' || $this->latest === '' || $this->request === '') {
            throw new InvalidArgumentException('API version header names cannot be empty.');
        }
    }
}
