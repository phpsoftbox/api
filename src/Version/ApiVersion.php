<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Version;

use PhpSoftBox\Api\Version\Exception\InvalidApiVersionException;
use Stringable;

use function filter_var;
use function preg_match;
use function sprintf;
use function strlen;
use function trim;

use const FILTER_VALIDATE_INT;

final readonly class ApiVersion implements Stringable
{
    private const int MAX_LENGTH = 64;

    private function __construct(
        public int $major,
        public int $minor,
        public int $patch,
    ) {
    }

    public static function parse(string $value): self
    {
        $normalized = trim($value);
        if (
            $normalized === ''
            || strlen($normalized) > self::MAX_LENGTH
            || preg_match('/^(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)$/D', $normalized, $matches) !== 1
        ) {
            throw new InvalidApiVersionException($value);
        }

        $major = filter_var($matches[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        $minor = filter_var($matches[2], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        $patch = filter_var($matches[3], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($major === false || $minor === false || $patch === false) {
            throw new InvalidApiVersionException($value, 'API version segments exceed the supported integer range.');
        }

        return new self($major, $minor, $patch);
    }

    public function compare(self $other): int
    {
        return [$this->major, $this->minor, $this->patch] <=> [$other->major, $other->minor, $other->patch];
    }

    public function equals(self $other): bool
    {
        return $this->compare($other) === 0;
    }

    public function __toString(): string
    {
        return sprintf('%d.%d.%d', $this->major, $this->minor, $this->patch);
    }
}
