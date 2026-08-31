<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Tests;

use PhpSoftBox\Api\Tests\Fixtures\TestApiVersionEnum;
use PhpSoftBox\Api\Version\ApiVersion;
use PhpSoftBox\Api\Version\DefaultApiRequestVersionFactory;
use PhpSoftBox\Api\Version\EnumApiVersionPolicy;
use PhpSoftBox\Api\Version\Exception\InvalidApiVersionException;
use PhpSoftBox\Api\Version\Exception\UnsupportedApiVersionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApiVersion::class)]
#[CoversClass(DefaultApiRequestVersionFactory::class)]
#[CoversMethod(ApiVersion::class, 'parse')]
#[CoversMethod(ApiVersion::class, 'compare')]
#[CoversMethod(DefaultApiRequestVersionFactory::class, 'create')]
final class ApiVersionTest extends TestCase
{
    /**
     * Проверяет разбор полной стабильной версии и корректное числовое сравнение её сегментов.
     *
     * @see ApiVersion::parse()
     * @see ApiVersion::compare()
     */
    #[Test]
    public function parsesAndComparesStableFullVersions(): void
    {
        $first  = ApiVersion::parse('1.2.3');
        $second = ApiVersion::parse('1.10.0');

        self::assertSame('1.2.3', (string) $first);
        self::assertLessThan(0, $first->compare($second));
    }

    /**
     * Проверяет отклонение сокращённых, префиксных и нестабильных вариантов SemVer.
     *
     * @see ApiVersion::parse()
     */
    #[Test]
    public function rejectsNonCanonicalSemver(): void
    {
        foreach (['1', '1.2', 'v1.2.3', '01.2.3', '1.2.3-beta', 'latest'] as $invalid) {
            try {
                ApiVersion::parse($invalid);
                self::fail('Expected invalid API version: ' . $invalid);
            } catch (InvalidApiVersionException $exception) {
                self::assertSame($invalid, $exception->requestedValue);
            }
        }
    }

    /**
     * Проверяет выбор default-версии и отсутствие признака явного запроса при пустом входном значении.
     *
     * @see DefaultApiRequestVersionFactory::create()
     */
    #[Test]
    public function missingVersionSelectsConfiguredDefault(): void
    {
        $version = $this->factory()->create(null);

        self::assertSame(TestApiVersionEnum::VERSION_1_1_0, $version->selected());
        self::assertFalse($version->wasExplicitlyRequested());
        self::assertNull($version->requestedRaw());
    }

    /**
     * Проверяет точный выбор опубликованной версии и работу enum-based feature comparison.
     *
     * @see DefaultApiRequestVersionFactory::create()
     */
    #[Test]
    public function selectsOnlyExactlyPublishedVersion(): void
    {
        $version = $this->factory()->create('1.0.0');

        self::assertSame(TestApiVersionEnum::VERSION_1_0_0, $version->selected());
        self::assertTrue($version->wasExplicitlyRequested());
        self::assertSame('1.0.0', $version->requestedRaw());
        self::assertTrue($version->isLowerThan(TestApiVersionEnum::VERSION_1_1_0));
    }

    /**
     * Проверяет отклонение корректной SemVer, которая не входит в список опубликованных версий API.
     *
     * @see DefaultApiRequestVersionFactory::create()
     */
    #[Test]
    public function rejectsValidButUnpublishedVersion(): void
    {
        $this->expectException(UnsupportedApiVersionException::class);

        $this->factory()->create('1.0.5');
    }

    private function factory(): DefaultApiRequestVersionFactory
    {
        return new DefaultApiRequestVersionFactory(new EnumApiVersionPolicy(
            supportedVersions: TestApiVersionEnum::cases(),
            defaultVersion: TestApiVersionEnum::VERSION_1_1_0,
        ));
    }
}
