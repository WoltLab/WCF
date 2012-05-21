<?php
namespace wcf\acp\form;
use wcf\data\category\CategoryAction;
use wcf\data\category\CategoryEditor;
use wcf\data\category\CategoryNodeList;
use wcf\system\acl\ACLHandler;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Abstract implementation of a form to create categories.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
abstract class AbstractCategoryAddForm extends ACPForm {
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
	 * list with the category nodes
	 * @var	wcf\data\category\CategoryNodeList
	 */
	public $categoryNodeList = null;
	
	/**
	 * description of the category
	 * @var	string
	 */
	public $description = '';
	
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
	 * @var	wcf\data\object\type\ObjectType
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
	 * @see	wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'categoryAdd';
	
	/**
	 * title of the category
	 * @var	string
	 */
	public $title = '';
	
	/**
	 * @see	wcf\page\AbstractPage::assignVariables()
	 */
	public function __construct() {
		$classNameParts = explode('\\', get_called_class());
		$className = array_pop($classNameParts);
		
		// autoset controllers
		if (empty($this->addController)) {
			$this->addController = StringUtil::replace(array('AddForm', 'EditForm'), 'Add', $className);
		}
		if (empty($this->editController)) {
			$this->editController = StringUtil::replace(array('AddForm', 'EditForm'), 'Edit', $className);
		}
		if (empty($this->listController)) {
			$this->listController = StringUtil::replace(array('AddForm', 'EditForm'), 'List', $className);
		}
		
		parent::__construct();
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign(array(
			'aclObjectTypeID' => $this->aclObjectTypeID,
			'action' => 'add',
			'addController' => $this->addController,
			'categoryNodeList' => $this->categoryNodeList,
			'description' => $this->description,
			'editController' => $this->editController,
			'isDisabled' => $this->isDisabled,
			'listController' => $this->listController,
			'objectType' => $this->objectType,
			'parentCategoryID' => $this->parentCategoryID,
			'showOrder' => $this->showOrder,
			'title' => $this->title
		));
	}
	
	/**
	 * Reads the categories.
	 */
	protected function readCategories() {
		$this->categoryNodeList = new CategoryNodeList($this->objectType->objectTypeID, 0, true);
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
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {		
		$this->objectType = CategoryHandler::getInstance()->getObjectTypeByName($this->objectTypeName);
		if ($this->objectType === null) {
			throw new SystemException("Unknown category object type with name '".$this->objectTypeName."'");
		}
		
		// check permissions
		$this->checkCategoryPermissions();
		
		// get acl object type id
		if ($this->objectType->getProcessor()->getACLObjectTypeName()) {
			$this->aclObjectTypeID = ACLHandler::getInstance()->getObjectTypeID($this->objectType->getProcessor()->getACLObjectTypeName());
		}
		
		// autoset package id
		if (!$this->packageID) {
			$this->packageID = $this->objectType->packageID;
		}
		
		parent::readData();
		
		$this->readCategories();
	}
	
	/**
	 * @see	wcf\page\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (isset($_POST['description'])) {
			$this->description = StringUtil::trim($_POST['description']);
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
		if (isset($_POST['title'])) {
			$this->title = StringUtil::trim($_POST['title']);
		}
	}
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('description');
		I18nHandler::getInstance()->register('title');
	}
	
	/**
	 * @see	wcf\page\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new CategoryAction(array(), 'create', array(
			'data' => array(
				'description' => $this->description,
				'isDisabled' => $this->isDisabled,
				'objectTypeID' => $this->objectType->objectTypeID,
				'parentCategoryID' => $this->parentCategoryID,
				'showOrder' => $this->showOrder,
				'title' => $this->title
			)
		));
		$this->objectAction->executeAction();
		$returnValues = $this->objectAction->getReturnValues();
		
		if (!I18nHandler::getInstance()->isPlainValue('description') || !I18nHandler::getInstance()->isPlainValue('title')) {
			$categoryID = $returnValues['returnValues']->categoryID;
			
			$updateData = array();
			if (!I18nHandler::getInstance()->isPlainValue('description')) {
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
		}
		
		// reload cache
		CategoryHandler::getInstance()->reloadCache();
		
		// reset values
		$this->parentCategoryID = 0;
		$this->showOrder = 0;
		
		$this->saved();
		
		// disable assignment of i18n values
		I18nHandler::getInstance()->disableAssignValueVariables();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	wcf\page\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		$this->validateParentCategory();
	}
	
	/**
	 * Validates the parent category.
	 */
	protected function validateParentCategory() {
		if ($this->parentCategoryID) {
			if (CategoryHandler::getInstance()->getCategory($this->objectType->objectTypeID, $this->parentCategoryID) === null) {
				throw new UserInputException('parentCategoryID', 'invalid');
			}
		}
	}
}
