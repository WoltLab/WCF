<?php

namespace wcf\util;

use Psr\Http\Message\ResponseInterface;
use wcf\system\application\ApplicationHandler;
use wcf\system\event\EventHandler;
use wcf\system\request\RequestHandler;
use wcf\system\request\RouteHandler;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * Contains header-related functions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Util
 */
final class HeaderUtil
{
    /**
     * @deprecated 5.4 - gzip support was removed.
     */
    const GZIP_LEVEL = 1;

    /**
     * output HTML
     * @var string
     */
    public static $output = '';

    /**
     * Alias to php setcookie() function.
     *
     * @param string $name
     * @param string $value
     * @param int $expire
     */
    public static function setCookie($name, $value = '', $expire = 0)
    {
        $cookieDomain = self::getCookieDomain();

        $sameSite = '';
        if (!HTTP_SEND_X_FRAME_OPTIONS) {
            $sameSite = '; SameSite=none';
        }

        @\header(
            'Set-Cookie: ' . \rawurlencode(COOKIE_PREFIX . $name) . '=' . \rawurlencode((string)$value) . ($expire ? '; expires=' . \gmdate(
                'D, d-M-Y H:i:s',
                $expire
            ) . ' GMT; max-age=' . ($expire - TIME_NOW) : '') . '; path=/' . ($cookieDomain !== null ? '; domain=' . $cookieDomain : '') . (RouteHandler::secureConnection() ? '; secure' : '') . $sameSite . '; HttpOnly',
            false
        );
    }

    /**
     * Returns the cookie domain for the active application or 'null' if no domain should be specified.
     */
    public static function getCookieDomain(): ?string
    {
        $application = ApplicationHandler::getInstance()->getActiveApplication();
        $addDomain = (\mb_strpos(
            $application->cookieDomain,
            '.'
        ) === false || \str_ends_with(
            $application->cookieDomain,
            '.lan'
        ) || \str_ends_with($application->cookieDomain, '.local')) ? false : true;

        if (!$addDomain) {
            return null;
        }

        $cookieDomain = $application->cookieDomain;
        if ($addDomain && \strpos($cookieDomain, ':') !== false) {
            $cookieDomain = \explode(':', $cookieDomain, 2)[0];
        }

        return $cookieDomain;
    }

    /**
     * Sends the headers of a page.
     */
    public static function sendHeaders()
    {
        // send content type
        @\header('Content-Type: text/html; charset=UTF-8');

        // send no cache headers
        if (!PACKAGE_ID || !WCF::getSession()->spiderID) {
            self::sendNoCacheHeaders();
        }

        // send X-Frame-Options
        if (HTTP_SEND_X_FRAME_OPTIONS) {
            @\header('X-Frame-Options: SAMEORIGIN');
        }

        \ob_start([self::class, 'parseOutput']);
    }

    /**
     * Sends no cache headers.
     */
    public static function sendNoCacheHeaders()
    {
        @\header('Last-Modified: ' . \gmdate('D, d M Y H:i:s') . ' GMT');
        @\header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate');
    }

    /**
     * Returns a response matching the given response with headers preventing in-browser
     * caching attached.
     *
     * @since 5.5
     */
    public static function withNoCacheHeaders(ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withHeader(
                'cache-control',
                [
                    'max-age=0',
                    'no-cache',
                    'no-store',
                    'must-revalidate',
                ]
            )
            ->withHeader(
                'last-modified',
                \gmdate('D, d M Y H:i:s') . ' GMT'
            );
    }

    /**
     * @deprecated 5.4 - This method is a no-op, as gzip support was removed.
     */
    public static function exceptionDisableGzip()
    {
    }

    /**
     * Parses the rendered output.
     *
     * @param string $output
     * @return  string
     */
    public static function parseOutput($output)
    {
        self::$output = $output;

        if (!PACKAGE_ID || RequestHandler::getInstance()->isACPRequest()) {
            // force javascript relocation
            self::$output = \preg_replace('~<script([^>]*)>~', '<script data-relocate="true"\\1>', self::$output);
        }

        // move script tags to the bottom of the page
        $javascript = [];
        self::$output = \preg_replace_callback(
            '~<script data-relocate="true"(?P<script>.*?</script>)\s*~s',
            static function ($matches) use (&$javascript) {
                $javascript[] = '<script' . $matches['script'];

                return '';
            },
            self::$output
        );

        self::$output = \str_replace(
            '<!-- JAVASCRIPT_RELOCATE_POSITION -->',
            \implode("\n", $javascript),
            self::$output
        );

        // 3rd party plugins may differ the actual output before it is sent to the browser
        // please be aware, that $eventObj is not available here due to this being a static
        // class. Use HeaderUtil::$output to modify it.
        if (!\defined('NO_IMPORTS')) {
            EventHandler::getInstance()->fireAction(self::class, 'parseOutput');
        }

        return self::$output;
    }

    /**
     * Redirects the user agent to given location.
     *
     * @param string $location
     * @param bool $sendStatusCode
     * @param bool $temporaryRedirect
     */
    public static function redirect($location, $sendStatusCode = false, $temporaryRedirect = true)
    {
        // https://github.com/WoltLab/WCF/issues/2568
        if (SessionHandler::getInstance()->isFirstVisit()) {
            SessionHandler::getInstance()->register('__wcfIsFirstVisit', true);
        }

        if ($sendStatusCode) {
            if ($temporaryRedirect) {
                @\header('HTTP/1.1 307 Temporary Redirect');
            } else {
                @\header('HTTP/1.1 301 Moved Permanently');
            }
        }

        \header('cache-control: private');
        \header('Location: ' . $location);
    }

    /**
     * Does a delayed redirect.
     *
     * @param string $location
     * @param string $message
     * @param int $delay
     * @param string $status
     */
    public static function delayedRedirect($location, $message, $delay = 5, $status = 'success')
    {
        WCF::getTPL()->assign([
            'url' => $location,
            'message' => $message,
            'wait' => $delay,
            'templateName' => 'redirect',
            'templateNameApplication' => 'wcf',
            'status' => $status,
        ]);
        WCF::getTPL()->display('redirect');
    }

    /**
     * Forbid creation of HeaderUtil objects.
     */
    private function __construct()
    {
        // does nothing
    }
}
