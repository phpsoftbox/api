<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Version;

use InvalidArgumentException;

use function array_key_last;
use function array_map;
use function usort;

final class PublishedApiVersionSet
{
    private readonly ApiVersionPolicyInterface $policy;

    /** @var array<string, ApiVersionEnumInterface> */
    private array $casesByVersion = [];

    /** @var list<array{version: ApiVersion, case: ApiVersionEnumInterface}> */
    private array $sorted = [];

    public function __construct(ApiVersionPolicyInterface $policy)
    {
        $this->policy = $policy;

        $supported = $this->policy->supportedVersions();
        if ($supported === []) {
            throw new InvalidArgumentException('API version policy must publish at least one version.');
        }

        $default      = $this->policy->defaultVersion();
        $defaultClass = $default::class;

        foreach ($supported as $case) {
            if ($case::class !== $defaultClass) {
                throw new InvalidArgumentException('All published API versions must belong to the default version enum.');
            }

            $version = ApiVersion::parse($case->version());
            $key     = (string) $version;
            if (isset($this->casesByVersion[$key])) {
                throw new InvalidArgumentException('Duplicate published API version: ' . $key);
            }

            $this->casesByVersion[$key] = $case;
            $this->sorted[]             = ['version' => $version, 'case' => $case];
        }

        usort(
            $this->sorted,
            static fn (array $left, array $right): int => $left['version']->compare($right['version']),
        );

        $defaultVersion = (string) ApiVersion::parse($default->version());
        if (!isset($this->casesByVersion[$defaultVersion])) {
            throw new InvalidArgumentException('Default API version must be present in the published version list.');
        }
    }

    public function find(ApiVersion $version): ?ApiVersionEnumInterface
    {
        return $this->casesByVersion[(string) $version] ?? null;
    }

    public function minimum(): ApiVersionEnumInterface
    {
        return $this->sorted[0]['case'];
    }

    public function latest(): ApiVersionEnumInterface
    {
        return $this->sorted[array_key_last($this->sorted)]['case'];
    }

    /** @return list<string> */
    public function versionStrings(): array
    {
        return array_map(
            static fn (array $item): string => (string) $item['version'],
            $this->sorted,
        );
    }
}
