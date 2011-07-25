<?php
namespace wcf\system\exception;

/**
 * A UserException is thrown when a user gives invalid input data.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category 	Community Framework
 */
abstract class UserException extends \Exception implements IPrintableException {
	/**
	 * @see wcf\system\exception\IPrintableException::show()
	 */
	public function show() {
		echo '<pre>' . $this->getTraceAsString() . '</pre>';
	}
}
