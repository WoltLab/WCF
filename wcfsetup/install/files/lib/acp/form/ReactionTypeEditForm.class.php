<?php
namespace wcf\acp\form;
use wcf\data\reaction\type\ReactionType;
use wcf\system\exception\IllegalLinkException;
use wcf\system\file\upload\UploadFile;

/**
 * Represents the reaction type add form.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	5.2
 */
class ReactionTypeEditForm extends ReactionTypeAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.reactionType.list';
	
	/**
	 * @inheritDoc
	 */
	public $formAction = 'edit';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) {
			$this->formObject = new ReactionType($_REQUEST['id']);
			if (!$this->formObject->reactionTypeID) {
				throw new IllegalLinkException();
			}
		}
	}
	
	/**
	 * Sets the form data based on the current form object.
	 */
	protected function setFormObjectData() {
		parent::setFormObjectData(); 
		
		if (empty($_POST)) {
			$this->uploadFormField->value([
				new UploadFile(WCF_DIR . 'images/reaction/' . $this->formObject->iconFile, basename($this->formObject->iconFile), true, true, true)
			]);
		}
	}
}
