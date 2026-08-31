<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Tests;

use PhpSoftBox\Api\Tests\Fixtures\RecordingRequestHandler;
use PhpSoftBox\Api\Tests\Fixtures\TestApiVersionEnum;
use PhpSoftBox\Api\Version\ApiRequestVersionInterface;
use PhpSoftBox\Api\Version\ApiVersionMiddleware;
use PhpSoftBox\Api\Version\ApiVersionResponseHeaders;
use PhpSoftBox\Api\Version\DefaultApiRequestVersionFactory;
use PhpSoftBox\Api\Version\EnumApiVersionPolicy;
use PhpSoftBox\Api\Version\Exception\InvalidApiVersionException;
use PhpSoftBox\Api\Version\HeaderApiVersionResolver;
use PhpSoftBox\Http\Message\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApiVersionMiddleware::class)]
#[CoversClass(HeaderApiVersionResolver::class)]
#[CoversMethod(ApiVersionMiddleware::class, 'process')]
#[CoversMethod(HeaderApiVersionResolver::class, 'resolve')]
final class ApiVersionMiddlewareTest extends TestCase
{
    /**
     * Проверяет выбор опубликованной версии, её передачу в request и добавление version metadata в response.
     *
     * @see ApiVersionMiddleware::process()
     * @see HeaderApiVersionResolver::resolve()
     */
    #[Test]
    public function selectsVersionOnceAndPublishesResponseMetadata(): void
    {
        $handler = new RecordingRequestHandler(['Vary' => 'Accept-Encoding']);

        $middleware = $this->middleware();
        $response   = $middleware->process(
            new ServerRequest('GET', '/api', headers: ['X-CATALOG-API-VERSION' => '1.0.0']),
            $handler,
        );

        $received = $handler->receivedVersions[0] ?? null;
        self::assertInstanceOf(ApiRequestVersionInterface::class, $received);
        self::assertSame(TestApiVersionEnum::VERSION_1_0_0, $received->selected());
        self::assertSame('1.0.0', $response->getHeaderLine('X-CATALOG-API-VERSION'));
        self::assertSame('1.0.0', $response->getHeaderLine('X-CATALOG-API-VERSION-MIN'));
        self::assertSame('1.1.0', $response->getHeaderLine('X-CATALOG-API-VERSION-LATEST'));
        self::assertSame('Accept-Encoding, X-CATALOG-API-VERSION', $response->getHeaderLine('Vary'));
    }

    /**
     * Проверяет выбор настроенной default-версии, когда version header содержит только пробелы.
     *
     * @see ApiVersionMiddleware::process()
     * @see HeaderApiVersionResolver::resolve()
     */
    #[Test]
    public function emptyHeaderUsesDefaultVersion(): void
    {
        $response = $this->middleware()->process(
            new ServerRequest('GET', '/api', headers: ['X-CATALOG-API-VERSION' => '  ']),
            new RecordingRequestHandler(),
        );

        self::assertSame('1.1.0', $response->getHeaderLine('X-CATALOG-API-VERSION'));
    }

    /**
     * Проверяет отклонение запроса с несколькими непустыми значениями version header.
     *
     * @see HeaderApiVersionResolver::resolve()
     */
    #[Test]
    public function repeatedHeaderIsRejected(): void
    {
        $this->expectException(InvalidApiVersionException::class);

        $this->middleware()->process(
            new ServerRequest('GET', '/api', headers: ['X-CATALOG-API-VERSION' => ['1.0.0', '1.1.0']]),
            new RecordingRequestHandler(),
        );
    }

    /**
     * Проверяет, что последовательные запросы получают независимые экземпляры выбранной версии.
     *
     * @see ApiVersionMiddleware::process()
     */
    #[Test]
    public function sequentialRequestsDoNotShareSelectedVersionState(): void
    {
        $handler    = new RecordingRequestHandler();
        $middleware = $this->middleware();

        $middleware->process(
            new ServerRequest('GET', '/api', headers: ['X-CATALOG-API-VERSION' => '1.0.0']),
            $handler,
        );
        $middleware->process(
            new ServerRequest('GET', '/api', headers: ['X-CATALOG-API-VERSION' => '1.1.0']),
            $handler,
        );

        self::assertCount(2, $handler->receivedVersions);
        self::assertNotSame($handler->receivedVersions[0], $handler->receivedVersions[1]);
        self::assertSame(TestApiVersionEnum::VERSION_1_0_0, $handler->receivedVersions[0]?->selected());
        self::assertSame(TestApiVersionEnum::VERSION_1_1_0, $handler->receivedVersions[1]?->selected());
    }

    private function middleware(): ApiVersionMiddleware
    {
        $policy = new EnumApiVersionPolicy(
            TestApiVersionEnum::cases(),
            TestApiVersionEnum::VERSION_1_1_0,
        );

        return new ApiVersionMiddleware(
            resolver: new HeaderApiVersionResolver('X-CATALOG-API-VERSION'),
            factory: new DefaultApiRequestVersionFactory($policy),
            headers: new ApiVersionResponseHeaders(
                selected: 'X-CATALOG-API-VERSION',
                minimum: 'X-CATALOG-API-VERSION-MIN',
                latest: 'X-CATALOG-API-VERSION-LATEST',
            ),
        );
    }
}
