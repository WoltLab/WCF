<?php

namespace wcf\system\request\route;

/**
 * Default interface for route implementations.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
interface IRequestRoute
{
    /**
     * Builds a link upon route components.
     *
     * @param array<string, string> $components
     * @return string
     */
    public function buildLink(array $components);

    /**
     * Returns true if current route can handle the build request.
     *
     * @param array<string, string> $components
     * @return  bool
     */
    public function canHandle(array $components);

    /**
     * Returns parsed route data.
     *
     * @return array<string, string|bool>
     */
    public function getRouteData();

    /**
     * Returns true if route applies for ACP.
     *
     * @return  bool
     */
    public function isACP();

    /**
     * Returns true if given request url matches this route.
     *
     * @return  bool
     */
    public function matches(string $requestURL);
}
