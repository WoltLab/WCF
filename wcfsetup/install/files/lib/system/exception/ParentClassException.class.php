<?php

namespace wcf\system\exception;

/**
 * Exception implementation for cases when a class is expected to have a certain class
 * as a parent class but that is not the case.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ParentClassException extends \LogicException
{
    public function __construct(string $className, string $parentClassName)
    {
        parent::__construct("Class '{$className}' does not extend class '{$parentClassName}'.");
    }
}
