<?php

declare(strict_types=1);

namespace Laminas\HttpHandlerRunner;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * "Run" a request handler.
 *
 * The RequestHandlerRunner will marshal a request using the composed factory, and
 * then pass the request to the composed handler. Finally, it emits the response
 * returned by the handler using the composed emitter.
 *
 * If the factory for generating the request raises an exception or throwable,
 * then the runner will use the composed error response generator to generate a
 * response, based on the exception or throwable raised.
 */
final class RequestHandlerRunner implements RequestHandlerRunnerInterface
{
    /** @var Emitter\EmitterInterface */
    private $emitter;

    /**
     * A request handler to run as the application.
     *
     * @var RequestHandlerInterface
     */
    private $handler;

    /**
     * A factory capable of generating an error response in the scenario that
     * the $serverRequestFactory raises an exception during generation of the
     * request instance.
     *
     * The factory will receive the Throwable or Exception that caused the error,
     * and must return a Psr\Http\Message\ResponseInterface instance.
     *
     * @var callable(Throwable):ResponseInterface
     */
    private $serverRequestErrorResponseGenerator;

    /**
     * A factory capable of generating a Psr\Http\Message\ServerRequestInterface instance.
     * The factory will not receive any arguments.
     *
     * @var callable():ServerRequestInterface
     */
    private $serverRequestFactory;

    /**
     * @param callable():ServerRequestInterface     $serverRequestFactory
     * @param callable(Throwable):ResponseInterface $serverRequestErrorResponseGenerator
     */
    public function __construct(
        RequestHandlerInterface $handler,
        Emitter\EmitterInterface $emitter,
        callable $serverRequestFactory,
        callable $serverRequestErrorResponseGenerator
    ) {
        $this->handler = $handler;
        $this->emitter = $emitter;

        $this->serverRequestFactory                = $serverRequestFactory;
        $this->serverRequestErrorResponseGenerator = $serverRequestErrorResponseGenerator;
    }

    public function run(): void
    {
        try {
            $request = ($this->serverRequestFactory)();
        } catch (Throwable $e) {
            // Error in generating the request
            $this->emitMarshalServerRequestException($e);
            return;
        }

        $response = $this->handler->handle($request);

        $this->emitter->emit($response);
    }

    private function emitMarshalServerRequestException(Throwable $exception): void
    {
        $response = ($this->serverRequestErrorResponseGenerator)($exception);
        $this->emitter->emit($response);
    }
}
