<?php

namespace wcf\http\error;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Stores additional metadata for response generation for erroneous requests.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class ErrorDetail
{
    private const ATTRIBUTE = self::class;

    private function __construct(
        private ?string $message = null,
        private ?\Throwable $throwable = null,
    ) {
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getThrowable(): ?\Throwable
    {
        return $this->throwable;
    }

    /**
     * Creates a new ErrorDetail object with the Throwable's message
     * as the message.
     */
    public static function fromThrowable(\Throwable $e): self
    {
        return self::fromMessageWithThrowable($e->getMessage(), $e);
    }

    /**
     * Creates a new ErrorDetail object with the given message.
     */
    public static function fromMessage(string $message): self
    {
        return self::fromMessageWithThrowable(
            $message,
            new \Exception('Dummy Exception for context')
        );
    }

    /**
     * Creates a new ErrorDetail object with the given message and the
     * Throwable as context.
     */
    public static function fromMessageWithThrowable(string $message, \Throwable $e): self
    {
        return new self(
            $message,
            $e
        );
    }

    public function attachToRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withAttribute(self::ATTRIBUTE, $this);
    }

    public static function fromRequest(ServerRequestInterface $request): ?self
    {
        return $request->getAttribute(self::ATTRIBUTE);
    }
}
