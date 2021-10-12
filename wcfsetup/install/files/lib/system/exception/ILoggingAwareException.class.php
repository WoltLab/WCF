<?php

namespace wcf\system\exception;

/**
 * @deprecated 5.5 Either use 'IExtraInformationException' or include the necessary information within the message.
 */
interface ILoggingAwareException extends \Throwable
{
    /**
     * Called if the exception was logged into $logFile with ID $exceptionID.
     */
    public function finalizeLog(string $exceptionID, string $logFile): void;
}
