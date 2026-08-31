<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Error;

use InvalidArgumentException;

use function preg_match;
use function trim;

final readonly class ApiErrorDefinition
{
    public string $code;
    public string $title;
    public string $description;

    public function __construct(
        string $code,
        public int $status,
        string $title,
        string $description,
    ) {
        $this->code        = trim($code);
        $this->title       = trim($title);
        $this->description = trim($description);

        if ($this->code === '' || preg_match('/^[a-z][a-z0-9_]*$/D', $this->code) !== 1) {
            throw new InvalidArgumentException('API error code must use lower_snake_case.');
        }
        if ($this->status < 400 || $this->status > 599) {
            throw new InvalidArgumentException('API error status must be between 400 and 599.');
        }
        if ($this->title === '') {
            throw new InvalidArgumentException('API error title cannot be empty.');
        }
        if ($this->description === '') {
            throw new InvalidArgumentException('API error description cannot be empty.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->code === $other->code
            && $this->status === $other->status
            && $this->title === $other->title
            && $this->description === $other->description;
    }
}
