<?php
namespace wcf\acp\page;
use wcf\data\category\CategoryNodeList;
use wcf\page\AbstractPage;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\user\collapsible\content\UserCollapsibleContentHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Abstract implementation of a page with lists all categories of a certain object
 * type.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
abstract class AbstractCategoryListPage extends AbstractPage {
	/**
	 * name of the controller used to add new categories
	 * @var	string
	 */
	public $addController = '';
	
	/**
	 * category node list
	 * @var	wcf\data\category\CategoryNodeList
	 */
	public $categoryNodeList = null;
	
	/**
	 * ids of collapsed categories
	 * @var	array<integer>
	 */
	public $collapsedCategoryIDs = null;
	
	/**
	 * id of the collapsible category object type
	 * @var	integer
	 */
	public $collapsibleObjectTypeID = 0;
	
	/**
	 * name of the controller used to edit categories
	 * @var	string
	 */
	public $editController = '';
	
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
	 * @see	wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'categoryList';
	
	/**
	 * @see	wcf\page\AbstractPage::__run()
	 */
	public function __run() {
		$classNameParts = explode('\\', get_called_class());
		$className = array_pop($classNameParts);
		
		// autoset controllers
		if (empty($this->addController)) {
			$this->addController = StringUtil::replace('ListPage', 'Add', $className);
		}
		if (empty($this->editController)) {
			$this->editController = StringUtil::replace('ListPage', 'Edit', $className);
		}
		
		parent::__run();
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'addController' => $this->addController,
			'categoryNodeList' => $this->categoryNodeList,
			'collapsedCategoryIDs' => $this->collapsedCategoryIDs,
			'collapsibleObjectTypeID' => $this->collapsibleObjectTypeID,
			'editController' => $this->editController,
			'objectType' => $this->objectType
		));
	}
	
	/**
	 * Checks if the active user has the needed permissions to view this list.
	 */
	protected function checkCategoryPermissions() {
		if (!$this->objectType->getProcessor()->canDeleteCategory() && !$this->objectType->getProcessor()->canEditCategory()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Reads the categories.
	 */
	protected function readCategories() {
		$this->categoryNodeList = new CategoryNodeList($this->objectType->objectType, 0, true);
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
		
		$this->readCategories();
		
		// note that the implementation of wcf\system\category\ICategoryType
		// needs to support a object type of the pseudo definition
		// 'com.woltlab.wcf.collapsibleContent.acp' which has to be registered
		// during package installation as a 'com.woltlab.wcf.collapsibleContent'
		// object type if you want to support collapsible categories in the
		// acp; the pseudo object type is used to distinguish between
		// collapsible categories in the frontend and the acp
		$collapsibleObjectTypeName = $this->objectType->getProcessor()->getObjectTypeName('com.woltlab.wcf.collapsibleContent.acp');
		if ($collapsibleObjectTypeName) {
			$this->collapsibleObjectTypeID = UserCollapsibleContentHandler::getInstance()->getObjectTypeID($collapsibleObjectTypeName);
			// get ids of collapsed category
			if ($this->collapsibleObjectTypeID !== null) {
				$this->collapsedCategoryIDs = UserCollapsibleContentHandler::getInstance()->getCollapsedContent($this->collapsibleObjectTypeID);
				$this->collapsedCategoryIDs = array_flip($this->collapsedCategoryIDs);
			}
		}
		
		parent::readData();
	}
}
