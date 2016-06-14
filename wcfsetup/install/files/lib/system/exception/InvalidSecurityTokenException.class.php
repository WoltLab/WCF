<?php
namespace wcf\system\exception;
use wcf\system\WCF;

/**
 * An InvalidSecurityTokenException is thrown when the security token does not match the active session.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Exception
 */
class InvalidSecurityTokenException extends NamedUserException {
	/**
	 * Creates a new InvalidSecurityTokenException object.
	 */
	public function __construct() {
		parent::__construct(WCF::getLanguage()->get('wcf.ajax.error.sessionExpired'));
	}
}
