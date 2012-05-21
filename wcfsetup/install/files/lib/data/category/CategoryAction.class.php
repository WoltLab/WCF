<?php
namespace wcf\data\category;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\ValidateActionException;
use wcf\system\user\collapsible\content\UserCollapsibleContentHandler;
use wcf\system\WCF;

/**
 * Executes category-related actions.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category 	Community Framework
 */
class CategoryAction extends AbstractDatabaseObjectAction {
	/**
	 * categorized object type
	 * @var	wcf\data\object\type\ObjectType
	 */
	protected $objectType = null;
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::delete()
	 */
	public function delete() {
		$returnValue = parent::delete();
		
		// call category types
		foreach ($this->objects as $categoryEditor) {
			$categoryEditor->getCategoryType()->afterDeletion($categoryEditor);
		}
		
		return $returnValue;
	}
	
	/**
	 * Toggles the activity status of categories.
	 */
	public function toggle() {
		foreach ($this->objects as $categoryEditor) {
			$categoryEditor->update(array(
				'isDisabled' => 1 - $categoryEditor->isDisabled
			));
		}
	}
	
	/**
	 * Toggles the collapse status of categories.
	 */
	public function toggleContainer() {
		$objectTypeID = UserCollapsibleContentHandler::getInstance()->getObjectTypeID($this->objects[0]->getCategoryType()->getCollapsibleObjectTypeName());
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
	 * Updates the position of categories.
	 */
	public function updatePosition() {
		$showOrders = array();
		
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['data']['structure'] as $parentCategoryID => $categoryIDs) {
			if (!isset($showOrders[$parentCategoryID])) {
				$showOrders[$parentCategoryID] = 1;
			}
			
			foreach ($categoryIDs as $categoryID) {
				$this->objects[$categoryID]->update(array(
					'parentCategoryID' => $parentCategoryID ? $this->objects[$parentCategoryID]->objectTypeCategoryID : 0,
					'showOrder' => $showOrders[$parentCategoryID]++
				));
			}
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::validateDelete()
	 */
	public function validateCreate() {
		// validate permissions
		if (count($this->permissionsCreate)) {
			try {
				WCF::getSession()->checkPermissions($this->permissionsCreate);
			}
			catch (PermissionDeniedException $e) {
				throw new ValidateActionException('Insufficient permissions');
			}
		}
		
		if (!isset($this->parameters['data']['objectTypeID'])) {
			throw new ValidateActionException("Missing 'objectTypeID' data parameter");
		}
		
		$objectType = CategoryHandler::getInstance()->getObjectType($this->parameters['data']['objectTypeID']);
		if ($objectType === null) {
			throw new ValidateActionException("Unknown category object type with id '".$this->parameters['data']['objectTypeID']."'");
		}
		if (!$objectType->getProcessor()->canAddCategory()) {
			throw new ValidateActionException('Insufficient permissions');
		}
	}
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::validateDelete()
	 */
	public function validateDelete() {
		// validate permissions
		if (count($this->permissionsDelete)) {
			try {
				WCF::getSession()->checkPermissions($this->permissionsDelete);
			}
			catch (PermissionDeniedException $e) {
				throw new ValidateActionException('Insufficient permissions');
			}
		}
		
		// read objects
		if (!count($this->objects)) {
			$this->readObjects();
		}
		
		if (!count($this->objects)) {
			throw new ValidateActionException('Invalid object id');
		}
		
		foreach ($this->objects as $categoryEditor) {
			if (!$categoryEditor->getCategoryType()->canAddCategory()) {
				throw new ValidateActionException('Insufficient permissions');
			}
		}
	}
	
	/**
	 * Validates the 'toggle' action.
	 */
	public function validateToggle() {
		$this->validateUpdate();
	}
	
	/**
	 * Validates the 'toggleContainer' action.
	 */
	public function validateToggleContainer() {
		$this->validateUpdate();
	}
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::validateUpdate()
	 */
	public function validateUpdate() {
		// validate permissions
		if (count($this->permissionsUpdate)) {
			try {
				WCF::getSession()->checkPermissions($this->permissionsUpdate);
			}
			catch (PermissionDeniedException $e) {
				throw new ValidateActionException('Insufficient permissions');
			}
		}
		
		// read objects
		if (!count($this->objects)) {
			$this->readObjects();
		}
		
		if (!count($this->objects)) {
			throw new ValidateActionException('Invalid object id');
		}
		
		foreach ($this->objects as $categoryEditor) {
			if (!$categoryEditor->getCategoryType()->canEditCategory()) {
				throw new ValidateActionException('Insufficient permissions');
			}
		}
	}
	
	/**
	 * Validates the 'updatePosition' action.
	 */
	public function validateUpdatePosition() {
		// validate permissions
		if (count($this->permissionsUpdate)) {
			try {
				WCF::getSession()->checkPermissions($this->permissionsUpdate);
			}
			catch (PermissionDeniedException $e) {
				throw new ValidateActionException('Insufficient permissions');
			}
		}
		
		// validate 'structure' parameter
		if (!isset($this->parameters['data']['structure'])) {
			throw new ValidateActionException("Missing 'structure' parameter");
		}
		if (!is_array($this->parameters['data']['structure'])) {
			throw new ValidateActionException("'structure' parameter is no array");
		}
		
		// validate given category ids
		foreach ($this->parameters['data']['structure'] as $parentCategoryID => $categoryIDs) {
			if ($parentCategoryID) {
				// validate category
				$category = CategoryHandler::getInstance()->getCategoryByID($parentCategoryID);
				if ($category === null) {
					throw new ValidateActionException("Unknown category with id '".$parentCategoryID."'");
				}
				
				$this->objects[$category->categoryID] = new $this->className($category);
				
				// validate permissions
				if (!$category->getCategoryType()->canEditCategory()) {
					throw new ValidateActionException('Insufficient permissions');
				}
			}
			
			foreach ($categoryIDs as $categoryID) {
				// validate category
				$category = CategoryHandler::getInstance()->getCategoryByID($categoryID);
				if ($category === null) {
					throw new ValidateActionException("Unknown category with id '".$categoryID."'");
				}
				
				$this->objects[$category->categoryID] = new $this->className($category);
				
				// validate permissions
				if (!$category->getCategoryType()->canEditCategory()) {
					throw new ValidateActionException('Insufficient permissions');
				}
			}
		}
	}
}
