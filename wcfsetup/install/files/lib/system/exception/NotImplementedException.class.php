<?php
namespace wcf\system\exception;

/**
 * The exception that is thrown when a requested method or operation is not implemented.
 * 
 * The NotImplementedException exception indicates that the method or property that you
 * are attempting to invoke has no implementation and therefore provides no functionality.
 * As a result, you should not handle this error in a try/catch block. Instead, you should
 * remove the member invocation from your code.
 * 
 * @see		https://msdn.microsoft.com/en-US/library/system.notimplementedexception(v=vs.110).aspx
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Exception
 * @since	3.1
 */
class NotImplementedException extends \LogicException {
	/**
	 * NotImplementedException constructor.
	 */
	public function __construct() {
		parent::__construct("The invoked method has not been implemented yet.");
	}
}
