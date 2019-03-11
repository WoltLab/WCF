<?php
namespace wcf\system\database\exception;

/**
 * Denotes an database related error.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Exception
 * @since	3.0
 */
class DatabaseException extends \wcf\system\database\DatabaseException {
	/** @noinspection PhpMissingParentConstructorInspection */
	/**
	 * @inheritDoc
	 */
	public function __construct($message, \PDOException $previous = null) {
		\Exception::__construct($message, 0, $previous);

		// we cannot use the constructor's parameter, because of (http://php.net/manual/en/exception.getcode.php):
		// > Returns the exception code as integer in Exception but possibly as other type in Exception descendants (for example as string in PDOException).
		if ($previous) $this->code = $previous->code;
	}
}
