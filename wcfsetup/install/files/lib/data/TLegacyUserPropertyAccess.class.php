<?php
namespace wcf\data;

/**
 * Provides legacy access to the properties of the related user profile object.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User
 * @since	3.0
 * @deprecated	3.0
 */
trait TLegacyUserPropertyAccess {
	/**
	 * Returns the value of a object data variable with the given name.
	 * 
	 * @see	\wcf\data\IStorableObject::__get()
	 *
	 * @param	string		$name
	 * @return	mixed
	 */
	public function __get($name) {
		/** @noinspection PhpUndefinedClassInspection */
		/** @noinspection PhpUndefinedMethodInspection */
		$value = parent::__get($name);
		if ($value !== null) {
			return $value;
		}
		else if (!($this instanceof DatabaseObjectDecorator) && array_key_exists($name, $this->data)) {
			return null;
		}
		else if ($this instanceof DatabaseObjectDecorator && array_key_exists($name, $this->object->data)) {
			return null;
		}
		
		// in case any code should rely on directly accessing user properties,
		// refer them to the user profile object
		/** @noinspection PhpVariableVariableInspection */
		return $this->getUserProfile()->$name;
	}
}
