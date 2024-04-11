<?php

namespace wcf\system\endpoint;

/**
 * Represents the supported HTTP verbs for API endpoints.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
enum RequestMethod
{
    case DELETE;
    case GET;
    case POST;

    public function toString(): string
    {
        return match ($this) {
            self::DELETE => 'DELETE',
            self::GET => 'GET',
            self::POST => 'POST',
        };
    }
}
