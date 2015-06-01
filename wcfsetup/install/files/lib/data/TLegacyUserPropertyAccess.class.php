<?php
namespace wcf\data;

/**
 * Provides legacy access to the properties of the related user profile object.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user
 * @category	Community Framework
 * @deprecated
 */
trait TLegacyUserPropertyAccess {
	/**
	 * @see	\wcf\data\IStorableObject::__get()
	 */
	public function __get($name) {
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
		return $this->getUserProfile()->$name;
	}
}
