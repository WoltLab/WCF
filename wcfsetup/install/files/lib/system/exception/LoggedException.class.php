<?php

namespace wcf\system\exception;

// @codingStandardsIgnoreFile

/**
 * A logged exceptions prevents information disclosures and provides an easy
 * way to log errors.
 *
 * @author    Tim Duesterhus, Alexander Ebert
 * @copyright    2001-2019 WoltLab GmbH
 * @license    GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated    3.0 - Fatal Exceptions are logged automatically.
 */
class LoggedException extends \Exception
{
    protected string $exceptionID;

    /**
     * Returns exception id
     *
     * @return    string
     */
    public function getExceptionID()
    {
        if (empty($this->exceptionID)) {
            try {
                \wcf\functions\exception\logThrowable($this);
            } catch (\Throwable $e) {
            }
            $this->exceptionID = '*MAYDAY*';
        }

        return $this->exceptionID;
    }
}
