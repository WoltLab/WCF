<?php

namespace wcf\system\request;

use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\request\route\DynamicRequestRoute;
use wcf\system\request\route\IRequestRoute;
use wcf\system\request\route\LookupRequestRoute;
use wcf\system\SingletonFactory;
use wcf\util\FileUtil;

/**
 * Handles routes for HTTP requests.
 *
 * Inspired by routing mechanism used by ASP.NET MVC and released under the terms of
 * the Microsoft Public License (MS-PL) http://www.opensource.org/licenses/ms-pl.html
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class RouteHandler extends SingletonFactory
{
    /**
     * current host and protocol
     * @var string
     */
    private static $host = '';

    /**
     * current absolute path
     * @var string
     */
    private static $path = '';

    /**
     * current path info component
     * @var string
     */
    private static $pathInfo;

    /**
     * HTTP protocol, either 'http://' or 'https://'
     * @var string
     */
    private static $protocol = '';

    /**
     * HTTP encryption
     * @var bool
     */
    private static $secure;

    /**
     * true if the default controller is used (support for custom landing page)
     */
    private bool $isDefaultController = false;

    /**
     * true if the controller was renamed and has already been transformed
     */
    private bool $isRenamedController = false;

    /**
     * list of available routes
     * @var IRequestRoute[]
     */
    private array $routes = [];

    /**
     * parsed route data
     * @var array
     */
    private $routeData;

    /**
     * Sets default routes.
     */
    protected function init()
    {
        $route = new DynamicRequestRoute();
        $route->setIsACP(true);
        $this->addRoute($route);

        $route = new DynamicRequestRoute();
        $this->addRoute($route);

        $route = new LookupRequestRoute();
        $this->addRoute($route);

        // fire event
        EventHandler::getInstance()->fireAction($this, 'didInit');
    }

    /**
     * Adds a new route to the beginning of all routes.
     *
     * @param IRequestRoute $route
     */
    public function addRoute(IRequestRoute $route)
    {
        \array_unshift($this->routes, $route);
    }

    /**
     * Returns all registered routes.
     *
     * @return  IRequestRoute[]
     **/
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Returns true if a route matches. Please bear in mind, that the
     * first route that is able to consume all path components is used,
     * even if other routes may fit better. Route order is crucial!
     */
    public function matches(): bool
    {
        foreach ($this->routes as $route) {
            if (RequestHandler::getInstance()->isACPRequest() != $route->isACP()) {
                continue;
            }

            if ($route->matches(self::getPathInfo())) {
                $this->routeData = $route->getRouteData();

                $this->isDefaultController = $this->routeData['isDefaultController'];
                unset($this->routeData['isDefaultController']);

                $hasController = isset($this->routeData['controller']) && $this->routeData['controller'] !== '';
                if (
                    ($hasController && $this->isDefaultController())
                    || (!$hasController && !$this->isDefaultController())
                ) {
                    throw new \DomainException(\sprintf(
                        "Route implementation '%s' is buggy: Matched route must either be the default controller or a controller must be returned.",
                        $route::class
                    ));
                }

                if (isset($this->routeData['isRenamedController'])) {
                    $this->isRenamedController = $this->routeData['isRenamedController'];
                    unset($this->routeData['isRenamedController']);
                }

                $this->registerRouteData();

                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if route uses default controller.
     */
    public function isDefaultController(): bool
    {
        return $this->isDefaultController;
    }

    /**
     * Returns true if the controller was renamed and has already been transformed.
     */
    public function isRenamedController(): bool
    {
        return $this->isRenamedController;
    }

    /**
     * Returns parsed route data
     *
     * @return  array
     */
    public function getRouteData()
    {
        return $this->routeData;
    }

    /**
     * Registers route data within $_GET and $_REQUEST.
     */
    private function registerRouteData(): void
    {
        foreach ($this->routeData as $key => $value) {
            $_GET[$key] = $value;
            $_REQUEST[$key] = $value;
        }
    }

    /**
     * Builds a route based upon route components, this is nothing
     * but a reverse lookup.
     *
     * @param string $application application identifier
     * @param array $components
     * @param bool $isACP
     * @return  string
     * @throws  SystemException
     */
    public function buildRoute($application, array $components, $isACP = null)
    {
        if ($isACP === null) {
            $isACP = RequestHandler::getInstance()->isACPRequest();
        }
        $components['application'] = $application;

        foreach ($this->routes as $route) {
            if ($isACP != $route->isACP()) {
                continue;
            }

            if ($route->canHandle($components)) {
                return $route->buildLink($components);
            }
        }

        throw new SystemException("Unable to build route, no available route is satisfied.");
    }

    /**
     * Returns true if `$customUrl` contains only the letters a-z/A-Z, numbers, dashes,
     * underscores and forward slashes.
     *
     * All other characters including those from the unicode range are potentially unsafe,
     * especially when dealing with url rewriting and resulting encoding issues with some
     * webservers.
     *
     * This heavily limits the abilities for end-users to define appealing urls, but at
     * the same time this ensures a sufficient level of stability.
     *
     * @param string $customUrl url to perform sanity checks on
     * @return  bool    true if `$customUrl` passes the sanity check
     * @since   3.0
     */
    public static function isValidCustomUrl($customUrl): bool
    {
        return \preg_match('~^[a-z0-9\-_/]+$~', $customUrl) === 1;
    }

    /**
     * Returns true if this is a secure connection.
     */
    public static function secureConnection(): bool
    {
        if (self::$secure === null) {
            self::$secure = false;

            if (
                (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                || $_SERVER['SERVER_PORT'] == 443
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
            ) {
                self::$secure = true;
            }
        }

        return self::$secure;
    }

    /**
     * Returns true if the current environment is treated as a secure context by
     * browsers.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/Security/Secure_Contexts#when_is_a_context_considered_secure
     * @since 6.1
     */
    public static function secureContext(): bool
    {
        static $secureContext = null;
        if ($secureContext === null) {
            $secureContext = self::secureConnection();

            // The connection is considered as secure if it is encrypted with
            // TLS, or if the target host is a local address.
            if (!$secureContext) {
                $host = $_SERVER['HTTP_HOST'];

                // @see https://datatracker.ietf.org/doc/html/draft-ietf-dnsop-let-localhost-be-localhost-02
                if ($host === '127.0.0.1' || $host === 'localhost' || \str_ends_with($host, '.localhost')) {
                    $secureContext = true;
                }
            }
        }

        return $secureContext;
    }

    /**
     * Returns HTTP protocol, either 'http://' or 'https://'.
     */
    public static function getProtocol(): string
    {
        if (empty(self::$protocol)) {
            self::$protocol = 'http' . (self::secureConnection() ? 's' : '') . '://';
        }

        return self::$protocol;
    }

    /**
     * Returns protocol and domain name.
     */
    public static function getHost(): string
    {
        if (empty(self::$host)) {
            self::$host = self::getProtocol() . $_SERVER['HTTP_HOST'];
        }

        return self::$host;
    }

    /**
     * Returns absolute domain path.
     */
    public static function getPath(array $removeComponents = []): string
    {
        if (empty(self::$path)) {
            // dirname return a single backslash on Windows if there are no parent directories
            $dir = \dirname($_SERVER['SCRIPT_NAME']);
            self::$path = ($dir === '\\') ? '/' : FileUtil::addTrailingSlash($dir);
        }

        if (!empty($removeComponents)) {
            $path = \explode('/', self::$path);
            foreach ($path as $index => $component) {
                if (empty($path[$index])) {
                    unset($path[$index]);
                }

                if (\in_array($component, $removeComponents)) {
                    unset($path[$index]);
                }
            }

            return FileUtil::addTrailingSlash('/' . \implode('/', $path));
        }

        return self::$path;
    }

    /**
     * Returns current path info component.
     */
    public static function getPathInfo(): string
    {
        if (self::$pathInfo === null) {
            self::$pathInfo = '';

            if (!empty($_SERVER['QUERY_STRING'])) {
                // don't use parse_str as it replaces dots with underscores
                $components = \explode('&', $_SERVER['QUERY_STRING']);
                for ($i = 0, $length = \count($components); $i < $length; $i++) {
                    $component = $components[$i];

                    $pos = \mb_strpos($component, '=');
                    if ($pos !== false && $pos + 1 === \mb_strlen($component)) {
                        $component = \mb_substr($component, 0, -1);
                        $pos = false;
                    }

                    if ($pos === false) {
                        self::$pathInfo = \urldecode($component);
                        break;
                    }
                }
            }

            // translate legacy controller names
            if (\preg_match('~^(?P<controller>(?:[A-Z]+[a-z0-9]+)+)(?:/|$)~', self::$pathInfo, $matches)) {
                $parts = \preg_split(
                    '~([A-Z]+[a-z0-9]+)~',
                    $matches['controller'],
                    -1,
                    \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY
                );
                $parts = \array_map('strtolower', $parts);

                self::$pathInfo = \implode('-', $parts) . \mb_substr(
                    self::$pathInfo,
                    \mb_strlen($matches['controller'])
                );
            }
        }

        return self::$pathInfo;
    }
}
