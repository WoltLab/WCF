<?php

namespace wcf\system\user\authentication\oauth\exception;

/**
 * Thrown when the CSRF 'state' parameter cannot be validated.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Authentication\Oauth\Exception
 * @since   5.4
 */
final class StateValidationException extends \UnexpectedValueException
{
    public function __construct($message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
