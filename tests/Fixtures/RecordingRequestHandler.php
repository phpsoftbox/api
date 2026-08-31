<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Tests\Fixtures;

use PhpSoftBox\Api\Version\ApiRequestVersionInterface;
use PhpSoftBox\Http\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RecordingRequestHandler implements RequestHandlerInterface
{
    /** @var list<ApiRequestVersionInterface|null> */
    public array $receivedVersions = [];

    /** @param array<string, string|string[]> $responseHeaders */
    public function __construct(
        private readonly array $responseHeaders = [],
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $version = $request->getAttribute(ApiRequestVersionInterface::class);

        $this->receivedVersions[] = $version instanceof ApiRequestVersionInterface ? $version : null;

        return new Response(headers: $this->responseHeaders);
    }
}
