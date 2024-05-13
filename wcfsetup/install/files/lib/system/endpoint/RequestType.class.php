<?php

namespace wcf\system\endpoint;

/**
 * Defines the HTTP verb and route of an API endpoint.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class RequestType
{
    public function __construct(
        public readonly RequestMethod $method,
        public readonly string $uri,
    ) {
    }
}
