<?php

namespace wcf\system\endpoint;

/**
 * Shortcut attribute for API endpoints using POST.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class PostRequest extends RequestType
{
    public function __construct(string $uri)
    {
        parent::__construct(RequestMethod::POST, $uri);
    }
}
