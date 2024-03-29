<?php

namespace wcf\system\exception;

use wcf\system\WCF;

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
     */
    public function show()
    {
        if (WCF::debugModeIsEnabled()) {
            echo '<pre>' . $this->getTraceAsString() . '</pre>';
        } else {
            echo '<pre>' . $this->_getMessage() . '</pre>';
        }
    }

    /**
     * Returns the exception's message, should be used to sanitize the output.
     *
     * @return  string
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    protected function _getMessage()
    {
        return $this->getMessage();
    }
}
