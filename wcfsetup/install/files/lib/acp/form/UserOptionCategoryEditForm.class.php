<?php
namespace wcf\acp\form;
use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\option\category\UserOptionCategoryAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the form for editing user option categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class UserOptionCategoryEditForm extends UserOptionCategoryAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.option';
	
	/**
	 * category id
	 * @var	integer
	 */
	public $categoryID = 0;
	
	/**
	 * category object
	 * @var	\wcf\data\user\option\category\UserOptionCategory
	 */
	public $category = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->categoryID = intval($_REQUEST['id']);
		$this->category = new UserOptionCategory($this->categoryID);
		if (!$this->category->categoryID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		I18nHandler::getInstance()->save('categoryName', 'wcf.user.option.category.'.$this->category->categoryName, 'wcf.user.option');
		
		$this->objectAction = new UserOptionCategoryAction([$this->category], 'update', ['data' => array_merge($this->additionalFields, [
			'showOrder' => $this->showOrder
		])]);
		$this->objectAction->executeAction();
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		I18nHandler::getInstance()->setOptions('categoryName', 1, 'wcf.user.option.category.'.$this->category->categoryName, 'wcf.user.option.category.category\d+');
		
		if (!count($_POST)) {
			$this->showOrder = $this->category->showOrder;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign([
			'action' => 'edit',
			'categoryID' => $this->categoryID,
			'category' => $this->category
		]);
	}
}
