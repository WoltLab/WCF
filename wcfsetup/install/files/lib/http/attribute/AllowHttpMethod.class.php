<?php

namespace wcf\http\attribute;

/**
 * Allows accessing a controller with non-GET/POST methods. May also
 * be used to opt into explicit HEAD handling.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class AllowHttpMethod
{
    public function __construct(
        public readonly string $method
    ) {
        if (!\preg_match('/^[A-Z]+$/', $method)) {
            throw new \InvalidArgumentException("The given method '{$method}' is not valid.");
        }
    }
}
