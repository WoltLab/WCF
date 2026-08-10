<?php

namespace wcf\system\database\exception;

/**
 * Denotes a database related error.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class DatabaseException extends \wcf\system\database\DatabaseException
{
    public function __construct(string $message, ?\PDOException $previous = null)
    {
        \Exception::__construct($message, 0, $previous);

        // we cannot use the constructor's parameter, because of (http://php.net/manual/en/exception.getcode.php):
        // > Returns the exception code as integer in Exception but possibly as other type in Exception
        // descendants (for example as string in PDOException).
        if ($previous !== null) {
            $this->code = $previous->getCode();
        }
    }
}
