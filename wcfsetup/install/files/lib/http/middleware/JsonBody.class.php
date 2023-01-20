<?php

namespace wcf\http\middleware;

use GuzzleHttp\Psr7\Header;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\system\exception\SystemException;
use wcf\util\JSON;

/**
 * Decodes requests containing a JSON body.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class JsonBody implements MiddlewareInterface
{
    public const HAS_VALID_JSON_ATTRIBUTE = self::class . "\0hasValidJson";

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $hasValidJson = false;
        if ($this->contentTypeIsJson($request)) {
            try {
                $data = JSON::decode($request->getBody());
            } catch (SystemException $e) {
                return new TextResponse('Failed to decode the request body.', 400);
            }

            $request = $request->withParsedBody($data);

            $hasValidJson = true;
        }

        $request = $request->withAttribute(
            self::HAS_VALID_JSON_ATTRIBUTE,
            $hasValidJson,
        );

        return $handler->handle($request);
    }

    private function contentTypeIsJson(ServerRequestInterface $request): bool
    {
        $headers = Header::parse($request->getHeaderLine('content-type'));

        // If multiple content-type headers are given, we refuse to process any.
        if (\count($headers) !== 1) {
            return false;
        }

        $header = $headers[0];

        // The content-type itself is not part of a key-value pair and thus is numerically
        // indexed.
        if (!isset($header[0])) {
            return false;
        }

        // If the content-type is not application/json, we don't understand it.
        if ($header[0] !== 'application/json') {
            return false;
        }

        foreach ($header as $key => $value) {
            // If a charset pararameter exists ...
            if (\strtolower($key) === 'charset') {
                // ... and the charset is not UTF-8, we don't understand it.
                if (\strtolower($value) !== 'utf-8') {
                    return false;
                }
            }
        }

        // If no charset is given or the charset is UTF-8, we understand it.
        return true;
    }
}
