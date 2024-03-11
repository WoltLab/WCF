<?php

namespace wcf\action;

use CuyZ\Valinor\Mapper\MappingError;
use FastRoute\Dispatcher\Result\MethodNotAllowed;
use FastRoute\Dispatcher\Result\NotMatched;
use FastRoute\RouteCollector;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\system\endpoint\event\ControllerCollecting;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\endpoint\RequestType;
use wcf\system\event\EventHandler;
use wcf\system\request\RouteHandler;

use function FastRoute\simpleDispatcher;

final class ApiAction implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $targetAttribute = match ($request->getMethod()) {
            'GET' => GetRequest::class,
            'POST' => PostRequest::class,
            default => null,
        };

        if ($targetAttribute === null) {
            // TODO: debug response
            return new JsonResponse([
                'type' => 'invalid_request_error',
                'code' => 'method_not_allowed',
                'message' => 'The only supported verbs are "GET", "POST" and "DELETE".',
                'param' => '',
            ], 405);
        }

        $result = $this->parsePathInfo(RouteHandler::getPathInfo());
        if ($result === null) {
            \wcfDebug(RouteHandler::getPathInfo());
        }

        [$type, $prefix, $endpoint] = $result;

        // TODO: This is currently very inefficient and should be cached in some
        //       way, maybe even use a combined cache for both?
        $event = new ControllerCollecting($prefix);
        EventHandler::getInstance()->fire($event);

        $dispatcher = simpleDispatcher(
            static function (RouteCollector $r) use ($event) {
                foreach ($event->getControllers() as $controller) {
                    $reflectionClass = new \ReflectionClass($controller);
                    $attribute = current($reflectionClass->getAttributes(RequestType::class, \ReflectionAttribute::IS_INSTANCEOF));
                    \assert($attribute !== false);

                    $apiController = $attribute->newInstance();

                    $r->addRoute($apiController->method->toString(), $apiController->uri, $controller);
                }
            },
            [
                // TODO: debug only
                'cacheDisabled' => true,
            ]
        );

        $result = $dispatcher->dispatch($request->getMethod(), $endpoint);

        if ($result instanceof NotMatched) {
            // TODO: debug response
            return new JsonResponse([
                'type' => 'invalid_request_error',
                'type' => $type,
                'prefix' => $prefix,
                'endpoint' => $endpoint,
            ], 404);
        }

        if ($result instanceof MethodNotAllowed) {
            // TODO: debug response
            return new JsonResponse([
                'type' => 'invalid_request_error',
                'type' => $type,
                'prefix' => $prefix,
                'endpoint' => $endpoint,
            ], 405);
        }

        /** @var IController */
        $controller = $result->handler;

        try {
            return $controller($request, $result->variables);
        } catch (MappingError $e) {
            return new JsonResponse([
                'type' => 'invalid_request_error',
                'code' => 'mapping_error',
                'message' => $e->getMessage(),
                'param' => '',
            ], 400);
        }
    }

    /**
     * @return array{string, string, string}|null
     */
    private function parsePathInfo(string $pathInfo): array|null
    {
        if (!\str_starts_with($pathInfo, 'api/rpc/')) {
            return null;
        }

        $pathInfo = \mb_substr($pathInfo, \strlen('api/rpc/') - 1);

        $segments = \explode('/', $pathInfo);
        if (\count($segments) < 3) {
            // The namespace and the primary object are always required.
            return null;
        }

        return [
            'rpc',
            \sprintf(
                '/%s/%s',
                $segments[1],
                $segments[2],
            ),
            $pathInfo
        ];
    }
}
