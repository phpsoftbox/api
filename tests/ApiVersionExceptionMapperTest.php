<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Tests;

use PhpSoftBox\Api\Integration\Application\ApiVersionExceptionMapper;
use PhpSoftBox\Api\Tests\Fixtures\TestApiVersionEnum;
use PhpSoftBox\Api\Version\ApiVersionResponseHeaders;
use PhpSoftBox\Api\Version\EnumApiVersionPolicy;
use PhpSoftBox\Api\Version\Exception\InvalidApiVersionException;
use PhpSoftBox\Api\Version\Exception\UnsupportedApiVersionException;
use PhpSoftBox\Application\Exception\CodedHttpExceptionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApiVersionExceptionMapper::class)]
#[CoversMethod(ApiVersionExceptionMapper::class, 'map')]
final class ApiVersionExceptionMapperTest extends TestCase
{
    /**
     * Проверяет преобразование некорректного формата версии в безопасную coded HTTP-ошибку с metadata headers.
     *
     * @see ApiVersionExceptionMapper::map()
     */
    #[Test]
    public function mapsInvalidVersionToSafeCodedHttpException(): void
    {
        $invalid = $this->mapper()->map(new InvalidApiVersionException('latest'));
        self::assertInstanceOf(CodedHttpExceptionInterface::class, $invalid);
        self::assertSame(400, $invalid->statusCode());
        self::assertSame('invalid_api_version', $invalid->errorCode());
        self::assertSame('1.0.0', $invalid->headers()['X-CATALOG-API-VERSION-MIN'] ?? null);
        self::assertSame('1.1.0', $invalid->headers()['X-CATALOG-API-VERSION-LATEST'] ?? null);
        self::assertSame('X-CATALOG-API-VERSION', $invalid->headers()['Vary'] ?? null);
        self::assertArrayNotHasKey('X-CATALOG-API-VERSION', $invalid->headers());
    }

    /**
     * Проверяет преобразование неопубликованной версии в ошибку со списком поддерживаемых версий.
     *
     * @see ApiVersionExceptionMapper::map()
     */
    #[Test]
    public function mapsUnsupportedVersionWithPublishedVersionsDetails(): void
    {
        $unsupported = $this->mapper()->map(
            new UnsupportedApiVersionException('1.0.5', ['1.0.0', '1.1.0']),
        );
        self::assertInstanceOf(CodedHttpExceptionInterface::class, $unsupported);
        self::assertSame(406, $unsupported->statusCode());
        self::assertSame('unsupported_api_version', $unsupported->errorCode());
        self::assertSame(['supported_versions' => ['1.0.0', '1.1.0']], $unsupported->details());
    }

    private function mapper(): ApiVersionExceptionMapper
    {
        return new ApiVersionExceptionMapper(
            new EnumApiVersionPolicy(TestApiVersionEnum::cases(), TestApiVersionEnum::VERSION_1_1_0),
            new ApiVersionResponseHeaders(
                selected: 'X-CATALOG-API-VERSION',
                minimum: 'X-CATALOG-API-VERSION-MIN',
                latest: 'X-CATALOG-API-VERSION-LATEST',
            ),
        );
    }
}
