<?php

namespace wcf\http\middleware;

use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionAttribute;
use wcf\http\attribute\AllowHttpMethod;
use wcf\http\LegacyPlaceholderResponse;
use wcf\system\request\Request;
use wcf\system\request\RequestHandler;

/**
 * Validates the used HTTP method.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Http\Middleware
 * @since   6.0
 */
final class CheckHttpMethod implements MiddlewareInterface
{
    private const ALWAYS_ACCEPTABLE = ['GET', 'POST'];

    private readonly RequestHandler $requestHandler;

    public function __construct()
    {
        $this->requestHandler = RequestHandler::getInstance();
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (\in_array($request->getMethod(), self::ALWAYS_ACCEPTABLE, true)) {
            return $handler->handle($request);
        }

        $validMethods = $this->getValidMethods($this->requestHandler->getActiveRequest());

        if (\in_array($request->getMethod(), $validMethods, true)) {
            return $handler->handle($request);
        }

        if ($request->getMethod() === 'HEAD') {
            // RFC 9110#9.3.2:
            // > The HEAD method is identical to GET except that the server MUST NOT
            // > send content in the response.

            // We rewrite the HEAD method to GET if the controller does not explicitly
            // indicate support for HEAD. This allows the HEAD method to "just work",
            // but still allows controllers to opt into handling HEAD themselves, e.g.
            // to use an optimized implementation to generate the response.
            $response = $handler->handle(
                $request->withMethod('GET')
            );

            if ($response instanceof LegacyPlaceholderResponse) {
                return $response;
            }

            // While PHP will automatically strip the body for HEAD requests, we explicitly
            // replace the body with an empty one to not needlessly emit data that will
            // ultimately be dropped anyway.

            return $response->withBody(
                new Stream('php://memory', 'r')
            );
        }

        // HEAD is always valid, but handled specially by falling back to GET.
        // We must however add it to the list of valid methods to ensure it
        // is included in the 'allow' response header.
        $validMethods[] = 'HEAD';

        return new TextResponse('The used HTTP method is not allowed.', 405, [
            'allow' => \implode(', ', $validMethods),
        ]);
    }

    /**
     * @return string[]
     */
    private function getValidMethods(Request $request): array
    {
        $reflectionClass = new \ReflectionClass($request->getClassName());
        $attributes = \array_map(
            static fn (ReflectionAttribute $a) => $a->newInstance(),
            $reflectionClass->getAttributes(AllowHttpMethod::class)
        );

        return [
            ...self::ALWAYS_ACCEPTABLE,
            ...\array_map(
                static fn (AllowHttpMethod $a) => $a->method,
                $attributes
            ),
        ];
    }
}
