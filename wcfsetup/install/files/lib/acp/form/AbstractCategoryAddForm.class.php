<?php
namespace wcf\acp\form;
use wcf\data\category\CategoryAction;
use wcf\data\category\CategoryEditor;
use wcf\data\category\UncachedCategoryNodeTree;
use wcf\data\object\type\ObjectType;
use wcf\form\AbstractForm;
use wcf\system\acl\ACLHandler;
use wcf\system\category\CategoryHandler;
use wcf\system\category\CategoryPermissionHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Abstract implementation of a form to create categories.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
abstract class AbstractCategoryAddForm extends AbstractForm {
	/**
	 * id of the category acl object type
	 * @var	integer
	 */
	public $aclObjectTypeID = 0;
	
	/**
	 * name of the controller used to add new categories
	 * @var	string
	 */
	public $addController = '';
	
	/**
	 * additional category data
	 * @var	array
	 */
	public $additionalData = [];
	
	/**
	 * tree with the category nodes
	 * @var	UncachedCategoryNodeTree
	 */
	public $categoryNodeTree = null;
	
	/**
	 * indicates if the category is disabled
	 * @var	integer
	 */
	public $isDisabled = 0;
	
	/**
	 * name of the controller used to edit categories
	 * @var	string
	 */
	public $editController = '';
	
	/**
	 * name of the controller used to list the categories
	 * @var	string
	 */
	public $listController = '';
	
	/**
	 * category object type object
	 * @var	ObjectType
	 */
	public $objectType = null;
	
	/**
	 * name of the category object type
	 * @var	string
	 */
	public $objectTypeName = '';
	
	/**
	 * id of the package the created package belongs to
	 * @var	integer
	 */
	public $packageID = 0;
	
	/**
	 * language item with the page title
	 * @var	string
	 */
	public $pageTitle = 'wcf.category.add';
	
	/**
	 * id of the parent category id
	 * @var	integer
	 */
	public $parentCategoryID = 0;
	
	/**
	 * category show order
	 * @var	integer
	 */
	public $showOrder = 0;
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'categoryAdd';
	
	/**
	 * @inheritDoc
	 */
	public function __run() {
		$classNameParts = explode('\\', get_called_class());
		$className = array_pop($classNameParts);
		
		// autoset controllers
		if (empty($this->addController)) {
			$this->addController = str_replace(['AddForm', 'EditForm'], 'Add', $className);
		}
		if (empty($this->editController)) {
			$this->editController = str_replace(['AddForm', 'EditForm'], 'Edit', $className);
		}
		if (empty($this->listController)) {
			$this->listController = str_replace(['AddForm', 'EditForm'], 'List', $className);
		}
		
		parent::__run();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		if ($this->aclObjectTypeID) {
			ACLHandler::getInstance()->assignVariables($this->aclObjectTypeID);
		}
		
		WCF::getTPL()->assign([
			'aclObjectTypeID' => $this->aclObjectTypeID,
			'action' => 'add',
			'addController' => $this->addController,
			'additionalData' => $this->additionalData,
			'categoryNodeList' => $this->categoryNodeTree->getIterator(),
			'editController' => $this->editController,
			'isDisabled' => $this->isDisabled,
			'listController' => $this->listController,
			'objectType' => $this->objectType,
			'parentCategoryID' => $this->parentCategoryID,
			'showOrder' => $this->showOrder
		]);
		
		if ($this->pageTitle) {
			WCF::getTPL()->assign('pageTitle', $this->pageTitle);
		}
	}
	
	/**
	 * Checks if the active user has the needed permissions to add a new category.
	 */
	protected function checkCategoryPermissions() {
		if (!$this->objectType->getProcessor()->canAddCategory()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Reads the categories.
	 */
	protected function readCategories() {
		$this->categoryNodeTree = new UncachedCategoryNodeTree($this->objectType->objectType, 0, true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		$this->objectType = CategoryHandler::getInstance()->getObjectTypeByName($this->objectTypeName);
		if ($this->objectType === null) {
			throw new SystemException("Unknown category object type with name '".$this->objectTypeName."'");
		}
		
		// check permissions
		$this->checkCategoryPermissions();
		
		// get acl object type id
		$aclObjectTypeName = $this->objectType->getProcessor()->getObjectTypeName('com.woltlab.wcf.acl');
		if ($aclObjectTypeName) {
			$this->aclObjectTypeID = ACLHandler::getInstance()->getObjectTypeID($aclObjectTypeName);
		}
		
		// autoset package id
		if (!$this->packageID) {
			$this->packageID = $this->objectType->packageID;
		}
		
		if ($this->objectType->getProcessor()->hasDescription()) {
			I18nHandler::getInstance()->register('description');
		}
		I18nHandler::getInstance()->register('title');
		
		parent::readData();
		
		$this->readCategories();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (isset($_POST['additionalData'])) {
			$this->additionalData = ArrayUtil::trim($_POST['additionalData']);
		}
		if (isset($_POST['isDisabled'])) {
			$this->isDisabled = 1;
		}
		if (isset($_POST['parentCategoryID'])) {
			$this->parentCategoryID = intval($_POST['parentCategoryID']);
		}
		if (isset($_POST['showOrder'])) {
			$this->showOrder = intval($_POST['showOrder']);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new CategoryAction([], 'create', [
			'data' => array_merge($this->additionalFields, [
				'additionalData' => serialize($this->additionalData),
				'description' => ($this->objectType->getProcessor()->hasDescription() && I18nHandler::getInstance()->isPlainValue('description')) ? I18nHandler::getInstance()->getValue('description') : '',
				'isDisabled' => $this->isDisabled,
				'objectTypeID' => $this->objectType->objectTypeID,
				'parentCategoryID' => $this->parentCategoryID,
				'showOrder' => $this->showOrder > 0 ? $this->showOrder : null,
				'title' => I18nHandler::getInstance()->isPlainValue('title') ? I18nHandler::getInstance()->getValue('title') : ''
			])
		]);
		$this->objectAction->executeAction();
		$returnValues = $this->objectAction->getReturnValues();
		
		if (($this->objectType->getProcessor()->hasDescription() && !I18nHandler::getInstance()->isPlainValue('description')) || !I18nHandler::getInstance()->isPlainValue('title')) {
			$categoryID = $returnValues['returnValues']->categoryID;
			
			$updateData = [];
			if ($this->objectType->getProcessor()->hasDescription() && !I18nHandler::getInstance()->isPlainValue('description')) {
				$updateData['description'] = $this->objectType->getProcessor()->getI18nLangVarPrefix().'.description.category'.$categoryID;
				I18nHandler::getInstance()->save('description', $updateData['description'], $this->objectType->getProcessor()->getDescriptionLangVarCategory(), $this->packageID);
			}
			if (!I18nHandler::getInstance()->isPlainValue('title')) {
				$updateData['title'] = $this->objectType->getProcessor()->getI18nLangVarPrefix().'.title.category'.$categoryID;
				I18nHandler::getInstance()->save('title', $updateData['title'], $this->objectType->getProcessor()->getTitleLangVarCategory(), $this->packageID);
			}
			
			// update description/title
			$editor = new CategoryEditor($returnValues['returnValues']);
			$editor->update($updateData);
		}
		
		// save acl
		if ($this->aclObjectTypeID) {
			ACLHandler::getInstance()->save($returnValues['returnValues']->categoryID, $this->aclObjectTypeID);
			ACLHandler::getInstance()->disableAssignVariables();
			CategoryPermissionHandler::getInstance()->resetCache();
		}
		
		// reload cache
		CategoryHandler::getInstance()->reloadCache();
		$this->readCategories();
		
		// reset values
		$this->parentCategoryID = 0;
		$this->showOrder = 0;
		$this->additionalData = [];
		
		$this->saved();
		
		// reset i18n values
		I18nHandler::getInstance()->reset();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		$this->validateParentCategory();
		
		if (!I18nHandler::getInstance()->validateValue('title')) {
			if (I18nHandler::getInstance()->isPlainValue('title')) {
				throw new UserInputException('title');
			}
			else {
				throw new UserInputException('title', 'multilingual');
			}
		}
		
		if ($this->objectType->getProcessor()->hasDescription() && !I18nHandler::getInstance()->validateValue('description', false, !$this->objectType->getProcessor()->forceDescription())) {
			if (I18nHandler::getInstance()->isPlainValue('description')) {
				throw new UserInputException('description');
			}
			else {
				throw new UserInputException('description', 'multilingual');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateParentCategory() {
		if ($this->parentCategoryID) {
			if (!$this->objectType->getProcessor()->getMaximumNestingLevel()) {
				$this->parentCategoryID = 0;
				return;
			}
			
			$category = CategoryHandler::getInstance()->getCategory($this->parentCategoryID);
			if ($category === null) {
				throw new UserInputException('parentCategoryID', 'notValid');
			}
			
			if ($this->objectType->getProcessor()->getMaximumNestingLevel() != -1) {
				if (count($category->getParentCategories()) + 1 > $this->objectType->getProcessor()->getMaximumNestingLevel()) {
					throw new UserInputException('parentCategoryID', 'notValid');
				}
			}
		}
	}
}
