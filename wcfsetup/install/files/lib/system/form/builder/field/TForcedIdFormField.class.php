<?php
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\IFormNode;

/**
 * Forces all objects of a certain form field class to have the same id.
 * 
 * This trait is useful for specialized form fields that dictate naming conventions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
trait TForcedIdFormField {
	/**
	 * @inheritDoc
	 */
	public static function create(string $id = null): IFormNode {
		if ($id !== null) {
			throw new \InvalidArgumentException("This method does not expect any parameters. The id of form fields of this class is always '" . static::getForcedId() . "'.");
		}
		
		return parent::create(static::getForcedId());
	}
	
	/**
	 * Returns the id that every form field using this trait has.
	 * 
	 * @return	string		id of every form field using this trait
	 */
	abstract protected static function getForcedId(): string;
}