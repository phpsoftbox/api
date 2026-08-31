<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Version;

use PhpSoftBox\Api\Version\Exception\UnsupportedApiVersionException;

use function trim;

final readonly class DefaultApiRequestVersionFactory implements ApiRequestVersionFactoryInterface
{
    private ApiVersionPolicyInterface $policy;
    private PublishedApiVersionSet $versions;

    public function __construct(ApiVersionPolicyInterface $policy)
    {
        $this->policy   = $policy;
        $this->versions = new PublishedApiVersionSet($this->policy);
    }

    public function create(?string $requestedVersion): ApiRequestVersionInterface
    {
        $raw      = $requestedVersion === null ? null : trim($requestedVersion);
        $explicit = $raw !== null && $raw !== '';
        $selected = $this->policy->defaultVersion();

        if ($explicit) {
            $version  = ApiVersion::parse($raw);
            $selected = $this->versions->find($version);
            if ($selected === null) {
                throw new UnsupportedApiVersionException($raw, $this->versions->versionStrings());
            }
        }

        return new DefaultApiRequestVersion(
            selected: $selected,
            minimumSupported: $this->versions->minimum(),
            latestSupported: $this->versions->latest(),
            requestedRaw: $explicit ? $raw : null,
            explicitlyRequested: $explicit,
        );
    }
}
