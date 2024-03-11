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
use wcf\http\attribute\AllowHttpMethod;
use wcf\system\endpoint\event\ControllerCollecting;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\RequestFailure;
use wcf\system\endpoint\RequestType;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\request\RouteHandler;

use function FastRoute\simpleDispatcher;

#[AllowHttpMethod('DELETE')]
final class ApiAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $isSupportedVerb = match ($request->getMethod()) {
            'DELETE', 'GET', 'POST' => true,
            default => false,
        };

        if (!$isSupportedVerb) {
            return $this->toErrorResponse(RequestFailure::METHOD_NOT_ALLOWED, 'unacceptable_method');
        }

        $endpoint = $this->getEndpointFromPathInfo(RouteHandler::getPathInfo());
        if ($endpoint === null) {
            return $this->toErrorResponse(RequestFailure::UNKNOWN_ENDPOINT, 'missing_endpoint');
        }

        // TODO: This is currently very inefficient and should be cached in some
        //       way, maybe even use a combined cache for both?
        $event = new ControllerCollecting();
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
            return $this->toErrorResponse(RequestFailure::UNKNOWN_ENDPOINT, 'unknown_endpoint');
        }

        if ($result instanceof MethodNotAllowed) {
            return $this->toErrorResponse(RequestFailure::METHOD_NOT_ALLOWED, 'endpoint_does_not_allow_method');
        }

        /** @var IController */
        $controller = $result->handler;

        try {
            return $controller($request, $result->variables);
        } catch (MappingError $e) {
            return $this->toErrorResponse(RequestFailure::VALIDATION_FAILED, 'mapping_error', $e->getMessage());
        } catch (PermissionDeniedException) {
            return $this->toErrorResponse(RequestFailure::PERMISSION_DENIED, 'permission_denied');
        } catch (UserInputException $e) {
            return $this->toErrorResponse(RequestFailure::VALIDATION_FAILED, $e->getType(), $e->getMessage(), $e->getField());
        } catch (\Throwable $e) {
            return $this->toErrorResponse(RequestFailure::INTERNAL_ERROR, 'unknown_exception', $e->getMessage());
        }
    }

    private function getEndpointFromPathInfo(string $pathInfo): ?string
    {
        if (!\str_starts_with($pathInfo, 'api/rpc/')) {
            return null;
        }

        $endpoint = \mb_substr($pathInfo, \strlen('api/rpc/') - 1);

        // The namespace and the primary object are always required.
        if (\substr_count($endpoint, '/') < 2 || \str_ends_with($endpoint, '/')) {
            return null;
        }

        return $endpoint;
    }

    private function toErrorResponse(
        RequestFailure $reason,
        string $code,
        string $message = '',
        string $param = ''
    ): ResponseInterface {
        return new JsonResponse([
            'type' => $reason->toString(),
            'code' => $code,
            'message' => $message,
            'param' => $param,
        ], $reason->toStatusCode());
    }
}
