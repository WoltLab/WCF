<?php

namespace wcf\system\exception;

/**
 * Indicates that the exception should be let known if it was logged.
 *
 * @author      Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Exception
 * @since       5.4
 */
interface ILoggingAwareException extends \Throwable
{
    /**
     * Called if the exception was logged into $logFile with ID $exceptionID.
     */
    public function finalizeLog(string $exceptionID, string $logFile): void;
}
