<?php

namespace messere\partialResponseMiddleware;

use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class PartialResponseTest extends TestCase
{
    private const FILTER_NAME = 'myFilter';
    private const APPLICATION_JSON_CONTENT_TYPE = 'application/json;charset=utf-8';

    private function runDispatcher(string $content, string $contentType, string $fieldName, array $query): ResponseInterface
    {
        return Dispatcher::run([
            new PartialResponse($fieldName),
            function () use ($content, $contentType) {
                $response = Factory::createResponse();
                $response->getBody()->write($content);
                return $response->withHeader('Content-Type', $contentType);
            },
        ], Factory::createServerRequest([], 'GET', '/')->withQueryParams($query));
    }

    public function testPartialResponseConvertsObjectResponse(): void
    {
        $response = $this->runDispatcher(
            json_encode([ 'a' => 1, 'b' => 2 ]),
            static::APPLICATION_JSON_CONTENT_TYPE,
            static::FILTER_NAME,
            [static::FILTER_NAME => 'b']
        );

        $this->assertEquals(json_encode([ 'b' => 2 ]), (string) $response->getBody());
    }

    public function testPartialResponseConvertsArrayResponse(): void
    {
        $response = $this->runDispatcher(
            json_encode([ [ 'a' => 1, 'b' => 2 ], [ 'a' => 3, 'b' => 4 ] ]),
            static::APPLICATION_JSON_CONTENT_TYPE,
            static::FILTER_NAME,
            [static::FILTER_NAME => 'b']
        );

        $this->assertEquals(json_encode([ [ 'b' => 2 ], [ 'b' => 4 ] ]), (string) $response->getBody());
    }

    public function testPartialResponseIgnoresUnknownContentType(): void
    {
        $response = $this->runDispatcher(
            'test',
            'text/plain',
            'myPointlessFilter',
            ['myPointlessFilter' => 'test']
        );
        $this->assertEquals('test', (string) $response->getBody());
    }

    public function testPartialResponseDoesNothingIfFilterEmpty(): void
    {
        $response = $this->runDispatcher(
            json_encode([ 'a' => 1 ]),
            static::APPLICATION_JSON_CONTENT_TYPE,
            'emptyFilter',
            ['emptyFilter' => '']
        );
        $this->assertEquals([ 'a' => 1 ], (array)json_decode((string) $response->getBody()));
    }

    public function testPartialResponseDoesNothingIfFilterMissing(): void
    {
        $response = $this->runDispatcher(
            'test',
            'text/plain',
            'missingFilter',
            []
        );
        $this->assertEquals('test', (string) $response->getBody());
    }

    public function testPartialResponseDoesNothingIfJsonNotSupported(): void
    {
        $response = $this->runDispatcher(
            json_encode('test'),
            static::APPLICATION_JSON_CONTENT_TYPE,
            static::FILTER_NAME,
            [static::FILTER_NAME => 'test']
        );
        $this->assertEquals('"test"', (string) $response->getBody());
    }

    public function testPartialResponseReturnsBadRequestIfParsingFails(): void
    {
        $response = $this->runDispatcher(
            json_encode([ 'a' => 1 ]),
            static::APPLICATION_JSON_CONTENT_TYPE,
            static::FILTER_NAME,
            [static::FILTER_NAME => 'a(']
        );
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Invalid field selection a(', $response->getReasonPhrase());
    }
}
