<?php
namespace wcf\system\form\builder\field;

/**
 * Allows form fields to have a default id so that when creating form fields the
 * id does not have to be specified.
 * 
 * This trait is useful for frequently used specialized fields that generally have
 * the same id in different forms.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
trait TDefaultIdFormField {
	/**
	 * @inheritDoc
	 * @return	static
	 */
	public static function create($id = null) {
		if ($id === null) {
			$id = static::getDefaultId();
		}
		
		return parent::create($id);
	}
	
	/**
	 * Returns the default id of form fields using this trait.
	 * 
	 * @return	string		default id of form fields using this trait
	 */
	abstract protected static function getDefaultId();
}
