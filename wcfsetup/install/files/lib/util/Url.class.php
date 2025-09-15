<?php

namespace wcf\util;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/**
 * Generic wrapper around `parse_url()`.
 *
 * Unlike the base function that is used during processing, the method `Url::parse()`
 * will always provide a sane list of components, regardless if they're provided in
 * the `parse_url()`-output. You'll still need to check if the desired parameters
 * are non-empty.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.1
 * @phpstan-type Components array{
 *  scheme: string,
 *  host: string,
 *  port: int<0, 65535>,
 *  user: string,
 *  pass: string,
 *  path: string,
 *  query: string,
 *  fragment: string,
 * }
 * @implements \ArrayAccess<int|string, int|string>
 */
final class Url implements \ArrayAccess
{
    /**
     * list of url components
     * @var Components
     */
    private array $components;

    /**
     * maps properties to the array indices
     * @var array<int, string>
     */
    private static $propertyMap = [
        \PHP_URL_SCHEME => 'scheme',
        \PHP_URL_HOST => 'host',
        \PHP_URL_PORT => 'port',
        \PHP_URL_USER => 'user',
        \PHP_URL_PASS => 'pass',
        \PHP_URL_PATH => 'path',
        \PHP_URL_QUERY => 'query',
        \PHP_URL_FRAGMENT => 'fragment',
    ];

    /**
     * Tests if provided $url appears to be an URL.
     *
     * This method is a wrapper around filter_var with FILTER_VALIDATE_URL.
     */
    public static function is(string $url): bool
    {
        return \filter_var($url, \FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Parses the provided url and returns an array containing all possible url
     * components, even those not originally present, but in that case set to am
     * 'empty' value.
     */
    public static function parse(string $url): self
    {
        $url = \parse_url($url);
        if ($url === false) {
            $url = [];
        }

        return new self([
            'scheme' => (isset($url['scheme'])) ? $url['scheme'] : '',
            'host' => (isset($url['host'])) ? $url['host'] : '',
            'port' => (isset($url['port'])) ? $url['port'] : 0,
            'user' => (isset($url['user'])) ? $url['user'] : '',
            'pass' => (isset($url['pass'])) ? $url['pass'] : '',
            'path' => (isset($url['path'])) ? $url['path'] : '',
            'query' => (isset($url['query'])) ? $url['query'] : '',
            'fragment' => (isset($url['fragment'])) ? $url['fragment'] : '',
        ]);
    }

    /**
     * Returns true if the provided url contains all listed components and
     * that they're non-empty.
     *
     * @param int[] $components
     */
    public static function contains(string $url, array $components): bool
    {
        $result = self::parse($url);
        foreach ($components as $component) {
            if (empty($result[$component])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Url constructor, object creation is only allowed through `Url::parse()`.
     *
     * @param Components $components
     */
    private function __construct(array $components)
    {
        $this->components = $components;
    }

    #[\Override]
    public function offsetExists(mixed $offset): bool
    {
        // We're throwing an exception here, if `$offset` is an unknown property
        // key, which is a bit weird when working with `isset()` or `empty()`,
        // but any unknown key is a guaranteed programming error.
        //
        // On top of that, we'll only return true, if the value is actually non-
        // empty. That doesn't make much sense in combination with `isset()`, but
        // instead is used to mimic the legacy behavior of the array returned by
        // `parse_url()` with its missing keys.
        return !empty($this->components[$this->getIndex($offset)]);
    }

    #[\Override]
    public function offsetGet(mixed $offset): int|string
    {
        return $this->components[$this->getIndex($offset)];
    }

    #[\Override]
    public function offsetUnset(mixed $offset): void
    {
        throw new \RuntimeException("Url components are immutable");
    }

    #[\Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \RuntimeException("Url components are immutable");
    }

    /**
     * Attempts to resolve string properties and maps them to their int-based
     * component indices. Will throw an exception if the property is unknown,
     * making it easier to spot typos.
     *
     * @param mixed $property
     * @return      string
     * @throws      \RuntimeException
     */
    private function getIndex($property)
    {
        if (\is_int($property) && isset(self::$propertyMap[$property])) {
            return self::$propertyMap[$property];
        } elseif (\is_string($property) && isset($this->components[$property])) {
            return $property;
        }

        throw new \RuntimeException("Unknown url component offset '" . $property . "'.");
    }

    /**
     * Returns a matcher that decides whether the URL matches the given list of hostnames. Hostnames may contain an
     * optional wildcard prefix (`*.`).
     *
     * @since 5.4
     * @param string[] $hostnames
     * @return callable(string):bool
     */
    public static function getHostnameMatcher(array $hostnames): callable
    {
        $hosts = [];
        foreach ($hostnames as $host) {
            $isWildcard = false;
            if (\str_contains($host, '*')) {
                $host = \preg_replace('~^(\*\.)+~', '', $host);
                if (\str_contains($host, '*') || $host === '') {
                    // bad host
                    continue;
                }

                $isWildcard = true;
            }

            $host = \mb_strtolower($host);
            if (!isset($hosts[$host])) {
                $hosts[$host] = $isWildcard;
            }
        }

        return static function (string $hostname) use ($hosts): bool {
            static $validHosts = [];

            $hostname = \mb_strtolower($hostname);
            if (isset($hosts[$hostname]) || isset($validHosts[$hostname])) {
                return true;
            } else {
                // check wildcard hosts
                foreach ($hosts as $host => $isWildcard) {
                    if ($isWildcard && \str_contains($hostname, $host)) {
                        // the prepended dot will ensure that `example.com` matches only
                        // on domains like `foo.example.com` but not on `bar-example.com`
                        if (\str_ends_with($hostname, '.' . $host)) {
                            $validHosts[$hostname] = $hostname;

                            return true;
                        }
                    }
                }
            }

            return false;
        };
    }

    /**
     * Appends a query string and an optional fragment to an existing URI.
     *
     * @since 6.3
     */
    public static function withQueryString(string|Uri $uri, string $queryString): UriInterface
    {
        if (\is_string($uri)) {
            $uri = new Uri($uri);
        }

        if ($queryString === '') {
            return $uri;
        }

        $anchorPosition = \mb_strpos($queryString, '#');
        if ($anchorPosition !== false) {
            $anchor = \mb_substr($queryString, $anchorPosition + 1);
            $queryString = \mb_substr($queryString, 0, $anchorPosition);

            $uri = $uri->withFragment($anchor);
        }

        if ($queryString === '') {
            return $uri;
        }

        $parts = self::parseQueryString($queryString);

        return $uri->withQueryValues($uri, $parts);
    }

    /**
     * Parses a query string into a one-dimensional key-value list.
     *
     * This is a replacement for PHP’s `parse_str()` that has two problems:
     *  1. Dots inside the key are replaced by underscores.
     *  2. Keys containing square brackets are converted into nested arrays.
     *
     * @return array<string, string>
     * @since 6.3
     */
    public static function parseQueryString(string $queryString): array
    {
        $components = \explode('&', $queryString);
        $keyValueMap = [];
        for ($i = 0, $length = \count($components); $i < $length; $i++) {
            $component = $components[$i];
            if (\str_contains($component, '=')) {
                [$key, $value] = \explode('=', $component, 2);
            } else {
                $key = $component;
                $value = '';
            }

            $key = \str_replace(
                ['%5B', '%5D'],
                ['[', ']'],
                $key
            );

            $keyValueMap[$key] = $value;
        }

        return $keyValueMap;
    }
}
