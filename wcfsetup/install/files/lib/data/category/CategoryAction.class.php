<?php
namespace wcf\data\category;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\data\IToggleAction;
use wcf\data\IToggleContainerAction;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\user\collapsible\content\UserCollapsibleContentHandler;
use wcf\system\WCF;

/**
 * Executes category-related actions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category	Community Framework
 */
class CategoryAction extends AbstractDatabaseObjectAction implements ISortableAction, IToggleAction, IToggleContainerAction {
	/**
	 * categorized object type
	 * @var	\wcf\data\object\type\ObjectType
	 */
	protected $objectType = null;
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('create', 'delete', 'toggle', 'update', 'updatePosition');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::delete()
	 */
	public function delete() {
		$returnValue = parent::delete();
		
		// call category types
		foreach ($this->objects as $categoryEditor) {
			$categoryEditor->getProcessor()->afterDeletion($categoryEditor);
		}
		
		return $returnValue;
	}
	
	/**
	 * @see	\wcf\data\IToggleAction::toggle()
	 */
	public function toggle() {
		foreach ($this->objects as $categoryEditor) {
			$categoryEditor->update(array(
				'isDisabled' => 1 - $categoryEditor->isDisabled
			));
		}
	}
	
	/**
	 * @see	\wcf\data\IToggleContainerAction::toggleContainer()
	 */
	public function toggleContainer() {
		$collapsibleObjectTypeName = $this->objects[0]->getProcessor()->getObjectTypeName('com.woltlab.wcf.collapsibleContent');
		if ($collapsibleObjectTypeName === null) {
			throw new SystemException("Categories of this type don't support collapsing");
		}
		
		$objectTypeID = UserCollapsibleContentHandler::getInstance()->getObjectTypeID($collapsibleObjectTypeName);
		$collapsedCategories = UserCollapsibleContentHandler::getInstance()->getCollapsedContent($objectTypeID);
		
		$categoryID = $this->objects[0]->categoryID;
		if (array_search($categoryID, $collapsedCategories) !== false) {
			UserCollapsibleContentHandler::getInstance()->markAsOpened($objectTypeID, $categoryID);
		}
		else {
			UserCollapsibleContentHandler::getInstance()->markAsCollapsed($objectTypeID, $categoryID);
		}
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::update()
	 */
	public function update() {
		// check if showOrder needs to be recalculated
		if (count($this->objects) == 1 && isset($this->parameters['data']['parentCategoryID']) && isset($this->parameters['data']['showOrder'])) {
			if ($this->objects[0]->parentCategoryID != $this->parameters['data']['parentCategoryID'] || $this->objects[0]->showOrder != $this->parameters['data']['showOrder']) {
				$this->parameters['data']['showOrder'] = $this->objects[0]->updateShowOrder($this->parameters['data']['parentCategoryID'], $this->parameters['data']['showOrder']);
			}
		}
		
		parent::update();
		
		if (isset($this->parameters['data']['parentCategoryID'])) {
			$objectType = null;
			$parentUpdates = array();
			
			foreach ($this->objects as $category) {
				if ($objectType === null) {
					$objectType = $category->getObjectType();
				}
				
				if ($category->parentCategoryID != $this->parameters['data']['parentCategoryID']) {
					$parentUpdates[$category->categoryID] = array(
						'oldParentCategoryID' => $category->parentCategoryID,
						'newParentCategoryID' => $this->parameters['data']['parentCategoryID']
					);
				}
			}
			
			if (!empty($parentUpdates)) {
				$objectType->getProcessor()->changedParentCategories($parentUpdates);
			}
		}
	}
	
	/**
	 * @see	\wcf\data\ISortableAction::updatePosition()
	 */
	public function updatePosition() {
		$objectType = null;
		$parentUpdates = array();
		
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['data']['structure'] as $parentCategoryID => $categoryIDs) {
			$showOrder = 1;
			foreach ($categoryIDs as $categoryID) {
				$category = CategoryHandler::getInstance()->getCategory($categoryID);
				if ($objectType === null) {
					$objectType = $category->getObjectType();
				}
				
				if ($category->parentCategoryID != $parentCategoryID) {
					$parentUpdates[$categoryID] = array(
						'oldParentCategoryID' => $category->parentCategoryID,
						'newParentCategoryID' => $parentCategoryID
					);
				}
				
				$this->objects[$categoryID]->update(array(
					'parentCategoryID' => $parentCategoryID ? $this->objects[$parentCategoryID]->categoryID : 0,
					'showOrder' => $showOrder++
				));
			}
		}
		WCF::getDB()->commitTransaction();
		
		if (!empty($parentUpdates)) {
			$objectType->getProcessor()->changedParentCategories($parentUpdates);
		}
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::validateDelete()
	 */
	public function validateCreate() {
		$this->readInteger('objectTypeID', false, 'data');
		
		$objectType = CategoryHandler::getInstance()->getObjectType($this->parameters['data']['objectTypeID']);
		if ($objectType === null) {
			throw new UserInputException('objectTypeID', 'notValid');
		}
		if (!$objectType->getProcessor()->canAddCategory()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::validateDelete()
	 */
	public function validateDelete() {
		// read objects
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		foreach ($this->objects as $categoryEditor) {
			if (!$categoryEditor->getProcessor()->canDeleteCategory()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @see	\wcf\data\IToggleAction::validateToggle()
	 */
	public function validateToggle() {
		$this->validateUpdate();
	}
	
	/**
	 * @see	\wcf\data\IToggleContainerAction::validateToggleContainer()
	 */
	public function validateToggleContainer() {
		$this->validateUpdate();
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::validateUpdate()
	 */
	public function validateUpdate() {
		// read objects
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		foreach ($this->objects as $categoryEditor) {
			if (!$categoryEditor->getProcessor()->canEditCategory()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @see	\wcf\data\ISortableAction::validateUpdatePosition()
	 */
	public function validateUpdatePosition() {
		// validate 'structure' parameter
		if (!isset($this->parameters['data']['structure']) || !is_array($this->parameters['data']['structure'])) {
			throw new UserInputException('structure');
		}
		
		// validate given category ids
		foreach ($this->parameters['data']['structure'] as $parentCategoryID => $categoryIDs) {
			if ($parentCategoryID) {
				// validate category
				$category = CategoryHandler::getInstance()->getCategory($parentCategoryID);
				if ($category === null) {
					throw new UserInputException('structure');
				}
				
				// validate permissions
				if (!$category->getProcessor()->canEditCategory()) {
					throw new PermissionDeniedException();
				}
				
				$this->objects[$category->categoryID] = new $this->className($category);
			}
			
			foreach ($categoryIDs as $categoryID) {
				// validate category
				$category = CategoryHandler::getInstance()->getCategory($categoryID);
				if ($category === null) {
					throw new UserInputException('structure');
				}
				
				// validate permissions
				if (!$category->getProcessor()->canEditCategory()) {
					throw new PermissionDeniedException();
				}
				
				$this->objects[$category->categoryID] = new $this->className($category);
			}
		}
	}
}
