<?php

namespace wcf\http;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use Negotiation\Accept;
use Negotiation\Negotiator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use wcf\data\DatabaseObject;
use wcf\system\exception\ParentClassException;
use wcf\system\exception\UserInputException;
use wcf\util\StringUtil;

/**
 * Provides various helper methods for PSR-7/PSR-15 request processing.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class Helper
{
    /**
     * Returns whether the request's 'x-requested-with' header is equal
     * to 'XMLHttpRequest'.
     */
    public static function isAjaxRequest(RequestInterface $request): bool
    {
        return $request->getHeaderLine('x-requested-with') === 'XMLHttpRequest';
    }

    /**
     * Returns the user-agent in the request. If the header value is not
     * valid UTF-8, the bytes will be interpreted as ISO-8859-1 and converted
     * to UTF-8.
     */
    public static function getUserAgent(RequestInterface $request): ?string
    {
        if (!$request->hasHeader('user-agent')) {
            return null;
        }

        $userAgent = $request->getHeaderLine('user-agent');

        if (!StringUtil::isUTF8($userAgent)) {
            $userAgent = \mb_convert_encoding($userAgent, 'UTF-8', 'ISO-8859-1');
        }

        return $userAgent;
    }

    /**
     * Returns the URI's path and optional query, separated by a `?`.
     */
    public static function getPathAndQuery(UriInterface $uri): string
    {
        $result = $uri->getPath();

        if ($uri->getQuery() !== '') {
            $result .= '?' . $uri->getQuery();
        }

        return $result;
    }

    /**
     * Returns the MIME type the client prefers most from the input list of
     * $availableTypes. If multiple MIME types are equally acceptable, the
     * one that comes first in the input list will be returned. If no MIME
     * type is acceptable, the first item in the input list will be returned.
     *
     * Checks the request's 'accept' header.
     *
     * @param list<string> $availableTypes
     */
    public static function getPreferredContentType(RequestInterface $request, array $availableTypes): string
    {
        if (!$request->hasHeader('accept')) {
            // Anything is acceptable, use the server-preferred type.
            return $availableTypes[0];
        }

        $negotiator = new Negotiator();

        $best = $negotiator->getBest($request->getHeaderLine('accept'), $availableTypes);

        if ($best === null) {
            // Nothing is acceptable, use the server-preferred type.
            return $availableTypes[0];
        }

        \assert($best instanceof Accept);

        return $best->getValue();
    }

    /**
     * Validates query parameters against the provided schema. Unknown
     * keys are skipped and values are gracefully converted into the
     * requested types.
     *
     * The returned array will contain only the values specified in the
     * schema. Missing parameters or values that cannot be casted to the
     * requested type will yield a `MappingError`.
     *
     * @throws MappingError
     */
    public static function mapQueryParameters(array $queryParameters, string $schema): mixed
    {
        $mapper = (new MapperBuilder())
            ->allowSuperfluousKeys()
            ->enableFlexibleCasting()
            ->mapper();

        return $mapper->map(
            $schema,
            Source::array($queryParameters)
        );
    }

    /**
     * Validates body parameters against the provided schema. Expects
     * the data source to be JSON and thus values to be of the correct
     * data type. Unknown keys will be rejected.
     *
     * Missing parameters or values that cannot be casted to the requested
     * type will yield a `MappingError`.
     *
     * @throws MappingError
     */
    public static function mapRequestBody(array $bodyParameters, string $schema): mixed
    {
        $mapper = (new MapperBuilder())
            ->mapper();

        return $mapper->map(
            $schema,
            Source::array($bodyParameters)
        );
    }

    /**
     * Validates the query string parameters for `GET` and `DELETE` requests
     * based on the signature of the provided class name. For `POST` request
     * the parsed body is validated instead.
     *
     * @template T
     * @param class-string<T> $className
     * @return T
     * @throws MappingError
     * @since 6.1
     */
    public static function mapApiParameters(ServerRequestInterface $request, string $className): object
    {
        return match ($request->getMethod()) {
            'GET', 'DELETE' => self::mapQueryParameters($request->getQueryParams(), $className),
            'POST' => self::mapRequestBody($request->getParsedBody(), $className)
        };
    }

    /**
     * Fetch a database object using a parameter value in the URL.
     *
     * If the value does not resolve to an object, i.e. its object id is not
     * truthy, a UserInputException is thrown using the tables index name as the
     * field name.
     *
     * @template T
     * @param array<string, string> $variables
     * @param string-string<T> $className
     * @return T
     * @throws UserInputException
     * @since 6.1
     */
    public static function fetchObjectFromRequestParameter(array $variables, string $key, string $className): object
    {
        if (!\is_subclass_of($className, DatabaseObject::class)) {
            throw new ParentClassException($className, DatabaseObject::class);
        }

        if (!isset($variables[$key])) {
            throw new \RuntimeException(
                "The variable '{$key}' does not appear in the request variables, please check its spelling and if it appears in the route definition.",
            );
        }

        $dbo = new $className($variables[$key]);
        \assert($dbo instanceof DatabaseObject);

        if (!$dbo->getObjectID()) {
            throw new UserInputException(
                $dbo->getDatabaseTableIndexName(),
            );
        }

        return $dbo;
    }

    /**
     * Forbid creation of Helper objects.
     */
    private function __construct()
    {
        // does nothing
    }
}
