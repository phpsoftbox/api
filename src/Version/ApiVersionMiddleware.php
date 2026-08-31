<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Version;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_filter;
use function array_map;
use function array_unique;
use function array_values;
use function explode;
use function implode;
use function in_array;
use function strtolower;
use function trim;

final readonly class ApiVersionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ApiVersionResolverInterface $resolver,
        private ApiRequestVersionFactoryInterface $factory,
        private ApiVersionResponseHeaders $headers = new ApiVersionResponseHeaders(),
        private string $requestAttribute = ApiRequestVersionInterface::class,
    ) {
        if (trim($this->requestAttribute) === '') {
            throw new InvalidArgumentException('API version request attribute cannot be empty.');
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $version = $this->factory->create($this->resolver->resolve($request));
        $request = $request->withAttribute($this->requestAttribute, $version);

        $response = $handler->handle($request)
            ->withHeader($this->headers->selected, $version->selected()->version())
            ->withHeader($this->headers->minimum, $version->minimumSupported()->version())
            ->withHeader($this->headers->latest, $version->latestSupported()->version());

        return $this->withVary($response, $this->headers->request);
    }

    private function withVary(ResponseInterface $response, string $headerName): ResponseInterface
    {
        $vary = array_values(array_filter(array_map(
            static fn (string $value): string => trim($value),
            explode(',', $response->getHeaderLine('Vary')),
        )));

        if (in_array('*', $vary, true)) {
            return $response;
        }

        $normalized = array_map(strtolower(...), $vary);
        if (!in_array(strtolower($headerName), $normalized, true)) {
            $vary[] = $headerName;
        }

        return $response->withHeader('Vary', implode(', ', array_unique($vary)));
    }
}
