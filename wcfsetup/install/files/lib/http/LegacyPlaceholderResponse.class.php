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
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Http
 * @since   5.6
 */
final class LegacyPlaceholderResponse implements ResponseInterface
{
    private function throwException()
    {
        throw new \BadMethodCallException(\sprintf(
            "Operating on a '%s' placeholder return value of legacy controller is not allowed, as the controller's response information is not available.",
            self::class
        ));
    }

    public function getStatusCode()
    {
        $this->throwException();
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        $this->throwException();
    }

    public function getReasonPhrase()
    {
        $this->throwException();
    }

    public function getProtocolVersion()
    {
        $this->throwException();
    }

    public function withProtocolVersion($version)
    {
        $this->throwException();
    }

    public function getHeaders()
    {
        $this->throwException();
    }

    public function hasHeader($name)
    {
        $this->throwException();
    }

    public function getHeader($name)
    {
        $this->throwException();
    }

    public function getHeaderLine($name)
    {
        $this->throwException();
    }

    public function withHeader($name, $value)
    {
        $this->throwException();
    }

    public function withAddedHeader($name, $value)
    {
        $this->throwException();
    }

    public function withoutHeader($name)
    {
        $this->throwException();
    }

    public function getBody()
    {
        $this->throwException();
    }

    public function withBody(StreamInterface $body)
    {
        $this->throwException();
    }
}
