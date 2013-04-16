<?php
namespace wcf\acp\form;
use wcf\data\category\Category;
use wcf\data\category\CategoryAction;
use wcf\data\category\CategoryNodeTree;
use wcf\form\AbstractForm;
use wcf\system\acl\ACLHandler;
use wcf\system\category\CategoryHandler;
use wcf\system\category\CategoryPermissionHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Abstract implementation of a form to edit a category.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class AbstractCategoryEditForm extends AbstractCategoryAddForm {
	/**
	 * edited category
	 * @var	wcf\data\category\Category
	 */
	public $category = null;
	
	/**
	 * id of the edited category
	 * @var	integer
	 */
	public $categoryID = 0;
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'category' => $this->category
		));
	}
	
	/**
	 * @see	wcf\acp\form\AbstractCategoryAddForm::checkCategoryPermissions()
	 */
	protected function checkCategoryPermissions() {
		if (!$this->objectType->getProcessor()->canEditCategory()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @see	wcf\acp\form\AbstractCategoryAddForm::readCategories()
	 */
	protected function readCategories() {
		$this->categoryNodeTree = new CategoryNodeTree($this->objectType->objectType, 0, true, array($this->category->categoryID));
	}
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) {
			$this->categoryID = intval($_REQUEST['id']);
		}
		$this->category = new Category($this->categoryID);
		if (!$this->category->categoryID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			if ($this->objectType->getProcessor()->hasDescription()) {
				I18nHandler::getInstance()->setOptions('description', $this->packageID, $this->category->description, $this->objectType->getProcessor()->getI18nLangVarPrefix().'.description.category\d+');
			}
			I18nHandler::getInstance()->setOptions('title', $this->packageID, $this->category->title, $this->objectType->getProcessor()->getI18nLangVarPrefix().'.title.category\d+');
			
			$this->additionalData = $this->category->additionalData;
			$this->isDisabled = $this->category->isDisabled;
			$this->parentCategoryID = $this->category->parentCategoryID;
			$this->showOrder = $this->category->showOrder;
		}
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// handle description
		$description = '';
		if ($this->objectType->getProcessor()->hasDescription()) {
			$description = $this->objectType->getProcessor()->getI18nLangVarPrefix().'.description.category'.$this->category->categoryID;
			if (I18nHandler::getInstance()->isPlainValue('description')) {
				I18nHandler::getInstance()->remove($description, $this->packageID);
				$description = I18nHandler::getInstance()->getValue('description');
			}
			else {
				I18nHandler::getInstance()->save('description', $description, $this->objectType->getProcessor()->getDescriptionLangVarCategory(), $this->packageID);
			}
		}
		
		// handle title
		$title = $this->objectType->getProcessor()->getI18nLangVarPrefix().'.title.category'.$this->category->categoryID;
		if (I18nHandler::getInstance()->isPlainValue('title')) {
			I18nHandler::getInstance()->remove($title, $this->packageID);
			$title = I18nHandler::getInstance()->getValue('title');
		}
		else {
			I18nHandler::getInstance()->save('title', $title, $this->objectType->getProcessor()->getTitleLangVarCategory(), $this->packageID);
		}
		
		// update category
		$this->objectAction = new CategoryAction(array($this->category), 'update', array(
			'data' => array(
				'additionalData' => serialize($this->additionalData),
				'description' => $description,
				'isDisabled' => $this->isDisabled,
				'parentCategoryID' => $this->parentCategoryID,
				'showOrder' => $this->showOrder,
				'title' => $title
			)
		));
		$this->objectAction->executeAction();
		
		// update acl
		if ($this->aclObjectTypeID) {
			ACLHandler::getInstance()->save($this->category->categoryID, $this->aclObjectTypeID);
			CategoryPermissionHandler::getInstance()->resetCache();
		}
		
		// reload cache
		CategoryHandler::getInstance()->reloadCache();
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	wcf\acp\form\AbstractCategoryAddForm::validateParentCategory()
	 */
	protected function validateParentCategory() {
		parent::validateParentCategory();
		
		// check if new parent category is no child category of the category
		$childCategories = CategoryHandler::getInstance()->getChildCategories($this->categoryID, $this->objectType->objectTypeID);
		if (isset($childCategories[$this->parentCategoryID])) {
			throw new UserInputException('parentCategoryID', 'invalid');
		}
	}
}
