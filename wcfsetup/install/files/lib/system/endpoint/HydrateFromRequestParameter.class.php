<?php

namespace wcf\system\endpoint;

/**
 * Hydrates an object based on the parameter from the request URI. Performs a
 * check if the resulting object id is truthy.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class HydrateFromRequestParameter
{
    public function __construct(
        public readonly string $parameterName
    ) {
    }
}
