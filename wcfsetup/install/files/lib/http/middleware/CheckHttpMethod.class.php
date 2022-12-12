<?php

namespace wcf\http\middleware;

use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionAttribute;
use wcf\http\attribute\AllowHttpMethod;
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
    private const ALWAYS_ACCEPTABLE = ['GET', 'HEAD', 'POST'];

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
