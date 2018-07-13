<?php
declare(strict_types=1);
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\IFormNode;

/**
 * Allows form fields to have a default id so that when creating form fields the
 * id does not have to be specified.
 * 
 * This trait is useful for frequently used specialized fields that generally have
 * the same id in different forms.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
trait TDefaultIdFormField {
	/**
	 * @inheritDoc
	 * @return	static
	 */
	public static function create(string $id = null): IFormNode {
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
	abstract protected static function getDefaultId(): string;
}
