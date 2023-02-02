<?php

namespace wcf\http;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use GuzzleHttp\Psr7\Header;
use Psr\Http\Message\RequestInterface;

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

        $acceptedTypes = Header::parse($request->getHeaderLine('accept'));

        \usort($acceptedTypes, static function ($a, $b) {
            return ($b['q'] ?? 1) <=> ($a['q'] ?? 1);
        });

        foreach ($acceptedTypes as [$acceptedType]) {
            // A MIME type withut a slash is malformatted.
            if (!\str_contains($acceptedType, '/')) {
                continue;
            }

            [$major, $minor] = \explode('/', $acceptedType, 2);

            // A MIME type with major '*', but a non '*' minor is malformatted.
            if ($major === '*' && $minor !== '*') {
                continue;
            }

            foreach ($availableTypes as $availableType) {
                // An exact match is acceptable.
                if ($acceptedType === $availableType) {
                    return $availableType;
                }

                // If the minor is '*', then anything that starts with the major is acceptable.
                if ($minor === '*') {
                    if (\str_starts_with($availableType, $major . '/')) {
                        return $availableType;
                    }
                }

                // If the major is '*', then anything is acceptable.
                if ($major === '*') {
                    return $availableType;
                }
            }
        }

        // Nothing is acceptable, use the server-preferred type.
        return $availableTypes[0];
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
     * Forbid creation of Helper objects.
     */
    private function __construct()
    {
        // does nothing
    }
}
