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
 * @since   3.0
 */
class ErrorException extends SystemException
{
    /**
     * @inheritDoc
     * @var int
     */
    protected $severity;

    /**
     * @param string $message
     * @param int $code
     * @param int $severity
     * @param string $filename
     * @param int $lineno
     * @param ?\Exception $previous
     */
    public function __construct(
        $message = "",
        $code = 0,
        $severity = 1,
        $filename = __FILE__,
        $lineno = __LINE__,
        $previous = null
    ) {
        parent::__construct($message, $code, "", $previous);

        $this->severity = $severity;
    }

    /**
     * @return int
     */
    #[\Override]
    public function getSeverity()
    {
        return $this->severity;
    }
}
