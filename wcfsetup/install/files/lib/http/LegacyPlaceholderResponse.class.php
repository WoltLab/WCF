<?php

namespace wcf\http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * The LegacyPlaceholderResponse is used as the return value for legacy controllers
 * that have not yet been adjusted to return a proper PSR-7 ResponseInterface.
 *
 * Any operations on a LegacyPlaceholderResponse are illegal, as neither the response body,
 * nor any headers set by the legacy controller can be reliably captured.
 *
 * Logic in the request pipeline that acts on a ResponseInterface must check for a LegacyPlaceholderResponse
 * and handle it separately.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class LegacyPlaceholderResponse implements ResponseInterface
{
    private function throwException(): never
    {
        throw new \BadMethodCallException(\sprintf(
            "Operating on a '%s' placeholder return value of legacy controller is not allowed, as the controller's response information is not available.",
            self::class
        ));
    }

    #[\Override]
    public function getStatusCode(): never
    {
        $this->throwException();
    }

    #[\Override]
    public function withStatus(int $code, string $reasonPhrase = ''): never
    {
        $this->throwException();
    }

    #[\Override]
    public function getReasonPhrase(): never
    {
        $this->throwException();
    }

    #[\Override]
    public function getProtocolVersion(): never
    {
        $this->throwException();
    }

    #[\Override]
    public function withProtocolVersion(string $version): never
    {
        $this->throwException();
    }

    #[\Override]
    public function getHeaders(): never
    {
        $this->throwException();
    }

    #[\Override]
    public function hasHeader(string $name): never
    {
        $this->throwException();
    }

    #[\Override]
    public function getHeader(string $name): never
    {
        $this->throwException();
    }

    #[\Override]
    public function getHeaderLine(string $name): never
    {
        $this->throwException();
    }

    #[\Override]
    public function withHeader(string $name, $value): never
    {
        $this->throwException();
    }

    #[\Override]
    public function withAddedHeader(string $name, $value): never
    {
        $this->throwException();
    }

    #[\Override]
    public function withoutHeader(string $name): never
    {
        $this->throwException();
    }

    #[\Override]
    public function getBody(): never
    {
        $this->throwException();
    }

    #[\Override]
    public function withBody(StreamInterface $body): never
    {
        $this->throwException();
    }
}
