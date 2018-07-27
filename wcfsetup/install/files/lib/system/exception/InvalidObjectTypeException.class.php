<?php
namespace wcf\system\exception;

/**
 * Exception implementation for cases when an object type of a certain object type
 * definition is expected but the object type is of a different object type definition.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Exception
 * @since	3.0
 */
class InvalidObjectTypeException extends \UnexpectedValueException {
	/**
	 * InvalidObjectTypeException constructor.
	 * 
	 * @param	string		$objectType		invalid object type name
	 * @param	string		$definitionName		name of the required object type definition
	 */
	public function __construct($objectType, $definitionName) {
		parent::__construct("Invalid object type '{$objectType}' for definition '{$definitionName}'.");
	}
}
