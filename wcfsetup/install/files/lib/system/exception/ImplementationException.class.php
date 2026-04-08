<?php

namespace wcf\system\exception;

/**
 * Exception implementation for cases when a class is expected to implement a certain
 * interface but that is not the case.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ImplementationException extends \LogicException
{
    public function __construct(string $className, string $interfaceName)
    {
        parent::__construct("Class '{$className}' does not implement interface '{$interfaceName}'.");
    }
}
