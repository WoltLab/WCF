<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\Helper;
use wcf\system\endpoint\error\ControllerError;
use wcf\system\endpoint\error\RouteParameterError;
use wcf\system\endpoint\event\ControllerCollecting;
use wcf\system\endpoint\exception\ControllerMalformed;
use wcf\system\endpoint\exception\RouteParameterMismatch;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\Parameters;
use wcf\system\endpoint\PostRequest;
use wcf\system\endpoint\RequestType;
use wcf\system\event\EventHandler;
use wcf\system\request\RouteHandler;

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

        $event = new ControllerCollecting($prefix);
        EventHandler::getInstance()->fire($event);

        $method = null;
        $matches = [];
        foreach ($event->getControllers() as $controller) {
            $result = $this->findRequestedEndpoint($targetAttribute, $prefix, $endpoint, $controller);
            if ($result !== null) {
                [$method, $matches] = $result;

                break;
            }
        }

        if ($method === null) {
            // TODO: debug response
            return new JsonResponse([
                'type' => 'invalid_request_error',
                'type' => $type,
                'prefix' => $prefix,
                'endpoint' => $endpoint,
            ]);
        }

        try {
            return $this->forwardRequest($request, $controller, $method, $matches);
        } catch (ControllerMalformed $e) {
            \wcf\functions\exception\logThrowable($e);

            // TODO: proper wrapper?
            return new JsonResponse([
                'type' => 'api_error',
                'code' => $e->type,
                'message' => \ENABLE_DEBUG_MODE ? $e->getMessage() : '',
                'param' => '',
            ], 400);
        } catch (RouteParameterMismatch $e) {
            // TODO: proper wrapper?
            return new JsonResponse([
                'type' => 'invalid_request_error',
                'code' => $e->type,
                'message' => $e->getMessage(),
                'param' => $e->name,
            ], 400);
        }
    }

    private function forwardRequest(
        ServerRequestInterface $request,
        IController $controller,
        \ReflectionMethod $method,
        array $matches
    ): ResponseInterface {
        $parameters = \array_map(
            static function (\ReflectionParameter $parameter) use ($matches, $request) {
                $type = $parameter->getType();
                if ($type === null) {
                    throw new ControllerMalformed(
                        ControllerError::ParameterWithoutType,
                        $parameter,
                    );
                }

                if (!($type instanceof \ReflectionNamedType)) {
                    throw new ControllerMalformed(
                        ControllerError::ParameterTypeComplex,
                        $parameter,
                    );
                }

                if ($type->getName() === 'int' || $type->getName() === 'string') {
                    $value = $matches[$parameter->name] ?? null;
                    if ($value === null) {
                        throw new ControllerMalformed(
                            ControllerError::ParameterNotInUri,
                            $parameter,
                        );
                    }

                    if ($type->getName() === 'int') {
                        $value = (int)$value;
                        if ($value <= 0) {
                            throw new RouteParameterMismatch(
                                RouteParameterError::ExpectedPositiveInteger,
                                $parameter->name
                            );
                        }

                        return $value;
                    }

                    if ($type->getName() === 'string') {
                        $value = \trim($value);
                        if ($value === '') {
                            throw new RouteParameterMismatch(
                                RouteParameterError::ExpectedNonEmptyString,
                                $parameter->name
                            );
                        }

                        return $value;
                    }

                    throw new \LogicException('Unreachable');
                } else if ($type->getName() === ServerRequestInterface::class) {
                    return $request;
                }

                // Support the mapping of parameters based on the request type.
                $mappingAttribute = current($parameter->getAttributes(Parameters::class));
                if ($mappingAttribute !== false) {
                    if ($type->getName() === 'array') {
                        $classStringOrShape = $mappingAttribute->newInstance()->arrayShape;
                    } else {
                        $classStringOrShape = $type->getName();
                    }

                    if ($request->getMethod() === 'GET' || $request->getMethod() === 'DELETE') {
                        return Helper::mapQueryParameters(
                            $request->getQueryParams(),
                            $classStringOrShape,
                        );
                    } else {
                        return Helper::mapRequestBody(
                            $request->getParsedBody(),
                            $classStringOrShape,
                        );
                    }
                }

                throw new ControllerMalformed(
                    ControllerError::ParameterTypeUnknown,
                    $parameter,
                );
            },
            $method->getParameters(),
        );

        return $controller->{$method->name}(...$parameters);
    }

    /**
     * @template T of RequestType
     * @param class-string<T> $targetAttribute
     * @return array{\ReflectionMethod, array{string: string}}|null
     */
    private function findRequestedEndpoint(
        string $targetAttribute,
        string $prefix,
        string $endpoint,
        IController $controller
    ): array|null {
        $reflectionClass = new \ReflectionClass($controller);
        $publicMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($publicMethods as $method) {
            $reflectionAttribute = \current($method->getAttributes($targetAttribute));
            if ($reflectionAttribute === false) {
                continue;
            }

            $attribute = $reflectionAttribute->newInstance();
            if (!\str_starts_with($attribute->uri, $prefix)) {
                continue;
            }

            $matches = $this->getMatchesFromUri($attribute, $endpoint);
            if ($matches === null) {
                continue;
            }

            return [
                $method,
                $matches,
            ];
        }

        return null;
    }

    private function getMatchesFromUri(RequestType $request, string $endpoint): array|null
    {
        $segments = \explode('/', $request->uri);

        $keys = [];
        foreach ($segments as &$segment) {
            if ($segment === '') {
                continue;
            }

            if (!\str_starts_with($segment, ':')) {
                continue;
            }

            $key = \substr($segment, 1);
            $keys[] = $key;

            $segment = \sprintf(
                '(?<%s>[^/]++)',
                $key,
            );
        }
        unset($segment);

        $pattern = '~^' . \implode('/', $segments) . '$~';
        if (\preg_match($pattern, $endpoint, $matches)) {
            return \array_filter(
                $matches,
                static fn (string $key) => \in_array($key, $keys),
                \ARRAY_FILTER_USE_KEY,
            );
        }

        return null;
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
