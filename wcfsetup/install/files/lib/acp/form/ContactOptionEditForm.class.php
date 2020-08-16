<?php
namespace wcf\acp\form;

/**
 * Shows the contact option edit form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since       3.1
 */
class ContactOptionEditForm extends ContactOptionAddForm {
	/**
	 * @inheritDoc
	 */
	public $action = 'edit';
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractCustomOptionForm::save();
	}
}
