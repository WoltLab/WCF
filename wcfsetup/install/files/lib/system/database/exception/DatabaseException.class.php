<?php
namespace wcf\system\database\exception;

/**
 * Denotes an database related error.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database.exception
 * @category	Community Framework
 * @since	2.2
 */
class DatabaseException extends \wcf\system\database\DatabaseException {
	/**
	 * @see	\Exception::__construct()
	 */
	public function __construct($message, \PDOException $previous = null) {
		\Exception::__construct($message, 0, $previous);
	}
}
