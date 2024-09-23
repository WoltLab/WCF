<?php

namespace wcf\action;

use CuyZ\Valinor\Mapper\MappingError;
use FastRoute\ConfigureRoutes;
use FastRoute\Dispatcher\Result\MethodNotAllowed;
use FastRoute\Dispatcher\Result\NotMatched;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\event\endpoint\ControllerCollecting;
use wcf\http\attribute\AllowHttpMethod;
use wcf\system\cache\builder\ApiEndpointCacheBuilder;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\RequestFailure;
use wcf\system\endpoint\RequestType;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\request\RouteHandler;

use function FastRoute\cachedDispatcher;
use function wcf\functions\exception\logThrowable;

/**
 * Resolves and forwards API requests to the responsible controllers, exposing
 * a unified JSON-based response with a clearly defined behavior.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
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
            return $this->toErrorResponse(RequestFailure::MethodNotAllowed, 'unacceptable_method');
        }

        $endpoint = $this->getEndpointFromPathInfo(RouteHandler::getPathInfo());
        if ($endpoint === null) {
            return $this->toErrorResponse(RequestFailure::UnknownEndpoint, 'missing_endpoint');
        }

        $dispatcher = cachedDispatcher(
            static function (ConfigureRoutes $r) {
                $event = new ControllerCollecting();
                EventHandler::getInstance()->fire($event);

                foreach ($event->getControllers() as $controller) {
                    $reflectionClass = new \ReflectionClass($controller);
                    $attribute = current($reflectionClass->getAttributes(RequestType::class, \ReflectionAttribute::IS_INSTANCEOF));
                    \assert($attribute !== false);

                    $apiController = $attribute->newInstance();

                    $r->addRoute($apiController->method->toString(), $apiController->uri, $controller);
                }
            },
            [
                'cacheKey' => self::class,
                'cacheDriver' => ApiEndpointCacheBuilder::getInstance(),
            ]
        );

        $result = $dispatcher->dispatch($request->getMethod(), $endpoint);

        if ($result instanceof NotMatched) {
            return $this->toErrorResponse(RequestFailure::UnknownEndpoint, 'unknown_endpoint');
        }

        if ($result instanceof MethodNotAllowed) {
            return $this->toErrorResponse(RequestFailure::MethodNotAllowed, 'endpoint_does_not_allow_method');
        }

        /** @var IController */
        $controller = $result->handler;

        try {
            return $controller($request, $result->variables);
        } catch (MappingError $e) {
            return $this->toErrorResponse(RequestFailure::ValidationFailed, 'mapping_error', $e->getMessage());
        } catch (PermissionDeniedException) {
            return $this->toErrorResponse(RequestFailure::PermissionDenied, 'permission_denied');
        } catch (UserInputException $e) {
            return $this->toErrorResponse(RequestFailure::ValidationFailed, $e->getType(), $e->getMessage(), $e->getField());
        } catch (IllegalLinkException) {
            return $this->toErrorResponse(RequestFailure::ValidationFailed, 'assertion_failed');
        } catch (\Throwable $e) {
            logThrowable($e);

            return $this->toErrorResponse(RequestFailure::InternalError, 'unknown_exception', $e->getMessage());
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
