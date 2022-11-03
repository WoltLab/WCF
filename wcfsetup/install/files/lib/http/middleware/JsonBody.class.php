<?php

namespace wcf\http\middleware;

use Laminas\Diactoros\Response\JsonResponse;
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
 * @package WoltLabSuite\Core\Http\Middleware
 * @since 6.0
 */
final class JsonBody implements MiddlewareInterface
{
    public const HAS_VALID_JSON_ATTRIBUTE = self::class . "\0hasValidJson";

    private const EXPECTED_CONTENT_TYPE = 'application/json';

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $hasValidJson = false;
        if (
            $request->getHeaderLine('content-type') === self::EXPECTED_CONTENT_TYPE
            || \str_starts_with($request->getHeaderLine('content-type'), self::EXPECTED_CONTENT_TYPE)
        ) {
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
}
