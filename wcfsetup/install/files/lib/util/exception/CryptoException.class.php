<?php

namespace wcf\util\exception;

/**
 * Denotes failure to perform secure crypto.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class CryptoException extends \Exception
{
    /**
     * @param string $message
     * @param ?\Throwable $previous
     */
    public function __construct($message, $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
