<?php

namespace wcf\system\io;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use wcf\system\Regex;

/**
 * Factory for HTTP Clients.
 *
 * As of right now the factory returns objects that implement Guzzle's ClientInterface.
 * Even if Guzzle will remain the HTTP client of choice for the foreseeable future you should
 * strive to use PSR-7 objects instead of relying on Guzzle's shortcuts for best compatibility.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Io
 * @since   5.3
 */
final class HttpFactory
{
    /**
     * @var Client
     */
    private static $defaultClient;

    /**
     * Returns a RFC 7231#5.5.3 compatible user agent.
     */
    public static function getDefaultUserAgent(?string $comment = null): string
    {
        if ($comment) {
            if (!Regex::compile("^[a-zA-Z0-9_:/\\. -]+$")->match($comment)) {
                throw new InvalidArgumentException("Invalid comment for user agent given.");
            }

            return \sprintf('WoltLabSuite/%s (%s)', \wcf\getMinorVersion(), $comment);
        }

        return \sprintf('WoltLabSuite/%s', \wcf\getMinorVersion());
    }

    /**
     * Returns a reference to the default HTTP client.
     */
    public static function getDefaultClient(): ClientInterface
    {
        if (self::$defaultClient === null) {
            self::$defaultClient = static::makeClient();
        }

        return self::$defaultClient;
    }

    /**
     * Creates a new HTTP client with the given $timeout as the maximum
     * total transfer duration.
     *
     * @since 5.4
     * @see HttpFactory::makeClient()
     */
    public static function makeClientWithTimeout(float $timeout): ClientInterface
    {
        return static::makeClient([
            RequestOptions::TIMEOUT => $timeout,
        ]);
    }

    /**
     * Creates a new HTTP client.
     *
     * The HTTP proxy will automatically be enabled, unless
     * specifically removed by passing appropriate options.
     *
     * @see Client
     */
    public static function makeClient(array $options = []): ClientInterface
    {
        $defaults = [
            RequestOptions::PROXY => PROXY_SERVER_HTTP,
            RequestOptions::HEADERS => [],
            RequestOptions::TIMEOUT => 60,
        ];

        foreach ($defaults as $key => $value) {
            if (!\array_key_exists($key, $options)) {
                $options[$key] = $value;
            }
        }

        $foundUserAgent = false;
        foreach ($options[RequestOptions::HEADERS] as $headerName => $value) {
            if (\strtolower($headerName) === 'user-agent') {
                $foundUserAgent = true;
                break;
            }
        }
        if (!$foundUserAgent) {
            $options[RequestOptions::HEADERS]['user-agent'] = self::getDefaultUserAgent();
        }

        return new Client($options);
    }
}
