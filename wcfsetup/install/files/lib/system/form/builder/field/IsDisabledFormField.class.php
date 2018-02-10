<?php
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\IFormNode;

/**
 * Implementation of a form field for disabling an object.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class IsDisabledFormField extends BooleanFormField {
	/**
	 * @inheritDoc
	 */
	public static function create(string $id = null): IFormNode {
		if ($id !== null) {
			throw new \InvalidArgumentException("This method does not expect any parameters. The id of form fields of this class is always 'isDisabled'.");
		}
		
		return parent::create('isDisabled');
	}
}
