<?php
namespace wcf\acp\form;
use wcf\data\user\option\category\UserOptionCategoryAction;
use wcf\data\user\option\category\UserOptionCategoryEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the form for adding new user option categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class UserOptionCategoryAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.option.category.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.canManageUserOption'];
	
	/**
	 * category name
	 * @var	string
	 */
	public $categoryName = '';
	
	/**
	 * show order
	 * @var	integer
	 */
	public $showOrder = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('categoryName');
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (I18nHandler::getInstance()->isPlainValue('categoryName')) $this->categoryName = I18nHandler::getInstance()->getValue('categoryName');
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if (!I18nHandler::getInstance()->validateValue('categoryName', true)) {
			throw new UserInputException('categoryName', 'multilingual');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// save label
		$this->objectAction = new UserOptionCategoryAction([], 'create', ['data' => array_merge($this->additionalFields, [
			'parentCategoryName' => 'profile',
			'categoryName' => $this->categoryName,
			'showOrder' => $this->showOrder
		])]);
		$this->objectAction->executeAction();
		
		// update name
		$returnValues = $this->objectAction->getReturnValues();
		$categoryID = $returnValues['returnValues']->categoryID;
		I18nHandler::getInstance()->save('categoryName', 'wcf.user.option.category.category'.$categoryID, 'wcf.user.option');
		$categoryEditor = new UserOptionCategoryEditor($returnValues['returnValues']);
		$categoryEditor->update([
			'categoryName' => 'category'.$categoryID
		]);
		$this->saved();
		
		// reset values
		$this->categoryName = '';
		$this->showOrder = 0;
		
		I18nHandler::getInstance()->reset();
		
		// show success
		WCF::getTPL()->assign([
			'success' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'add',
			'categoryName' => $this->categoryName,
			'showOrder' => $this->showOrder
		]);
	}
}
