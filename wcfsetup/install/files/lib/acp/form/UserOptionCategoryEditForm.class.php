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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserOptionCategoryEditForm extends UserOptionCategoryAddForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
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
	 * @see	\wcf\page\IPage::readParameters()
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
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		I18nHandler::getInstance()->save('categoryName', 'wcf.user.option.category.'.$this->category->categoryName, 'wcf.user.option');
		
		$this->objectAction = new UserOptionCategoryAction(array($this->category), 'update', array('data' => array_merge($this->additionalFields, array(
			'showOrder' => $this->showOrder
		))));
		$this->objectAction->executeAction();
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		I18nHandler::getInstance()->setOptions('categoryName', 1, 'wcf.user.option.category.'.$this->category->categoryName, 'wcf.user.option.category.category\d+');
		
		if (!count($_POST)) {
			$this->showOrder = $this->category->showOrder;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'categoryID' => $this->categoryID,
			'category' => $this->category
		));
	}
}
