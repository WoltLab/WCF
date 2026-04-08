<?php

namespace wcf\system\exception;

/**
 * This is a custom implementation of the default \ErrorException.
 * It is used for backwards compatibility reasons. Do not rely on it
 * inheriting \wcf\system\exception\SystemException.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ErrorException extends SystemException
{
    /**
     * @inheritDoc
     * @var int
     */
    protected $severity;

    public function __construct(
        string $message = "",
        int $code = 0,
        int $severity = 1,
        string $filename = __FILE__,
        int $lineno = __LINE__,
        ?\Exception $previous = null
    ) {
        parent::__construct($message, $code, "", $previous);

        $this->severity = $severity;
    }

    /**
     * @return int
     */
    public function getSeverity()
    {
        return $this->severity;
    }
}
