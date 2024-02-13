<?php

namespace wcf\system\exception;

/**
 * Exception implementation for cases in which a class could not be found.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ClassNotFoundException extends \LogicException
{
    public function __construct(string $className)
    {
        parent::__construct("Unable to find class '{$className}'.");
    }
}
