<?php

namespace wcf\http\attribute;

/**
 * Allows accessing a controller with non-GET/HEAD/POST methods.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Http\Attribute
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
