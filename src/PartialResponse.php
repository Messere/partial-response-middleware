<?php

namespace messere\partialResponseMiddleware;

use messere\phpValueMask\Parser\Parser;
use messere\phpValueMask\Parser\ParserException;
use Middlewares\Utils\Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PartialResponse implements MiddlewareInterface
{
    private $queryFieldName;

    public function __construct(string $queryFieldName = 'fields')
    {
        $this->queryFieldName = $queryFieldName;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (stripos($response->getHeaderLine('Content-Type'), 'application/json') === 0) {
            $filterValue = $request->getQueryParams()[$this->queryFieldName] ?? '';
            if ('' !== $filterValue) {
                $body = json_decode((string)$response->getBody(), false);
                if ((\is_object($body) || \is_array($body)) && JSON_ERROR_NONE === json_last_error()) {
                    try {
                        $filter = (new Parser())->parse($filterValue);
                    } catch (ParserException $e) {
                        return (new Factory\ResponseFactory())->createResponse(
                            400,
                            "Invalid field selection $filterValue"
                        );
                    }
                    $stream = (new Factory\StreamFactory())->createStream(
                        json_encode($filter->filter($body))
                    );
                    $response = $response->withBody($stream);
                }
            }
        }

        return $response;
    }
}
