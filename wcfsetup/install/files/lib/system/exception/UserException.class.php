<?php

namespace wcf\system\exception;

/**
 * A UserException is thrown when a user gives invalid input data.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class UserException extends \Exception implements IPrintableException
{
    /**
     * @inheritDoc
     * @deprecated 6.3
     */
    public function show()
    {
    }
}
