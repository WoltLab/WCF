<?php

declare(strict_types=1);

namespace wcf\http;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitterTrait;
use Psr\Http\Message\ResponseInterface;

use function flush;

/**
 * This emitter implementation is a copy of `Laminas\HttpHandlerRunner\Emitter\
 * SapiStreamEmitter` with the range handling being ripped out. The handling
 * of the `content-range` must be handled by the creator of the range itself.
 *
 * See https://github.com/laminas/laminas-httphandlerrunner/issues/22
 */
class SapiStreamEmitter implements EmitterInterface
{
    use SapiEmitterTrait;

    public function __construct(
        /** @param int Maximum output buffering size for each iteration. */
        private int $maxBufferLength = 8192
    ) {}

    /**
     * Emits a response for a PHP SAPI environment.
     *
     * Emits the status line and headers via the header() function, and the
     * body content via the output buffer.
     */
    #[\Override]
    public function emit(ResponseInterface $response): bool
    {
        $this->assertNoPreviousOutput();
        $this->emitHeaders($response);
        $this->emitStatusLine($response);

        flush();

        $this->emitBody($response);

        return true;
    }

    /**
     * Emit the message body.
     */
    private function emitBody(ResponseInterface $response): void
    {
        $body = $response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        if (! $body->isReadable()) {
            echo $body;
            return;
        }

        while (! $body->eof()) {
            echo $body->read($this->maxBufferLength);
        }
    }
}
