<?php
namespace wcf\data\category;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ICollapsibleContainerAction;
use wcf\data\IPositionAction;
use wcf\data\IToggleAction;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
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
class CategoryAction extends AbstractDatabaseObjectAction implements ICollapsibleContainerAction, IPositionAction, IToggleAction {
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
	 * @see	wcf\data\IToggleAction::toggle()
	 */
	public function toggle() {
		foreach ($this->objects as $categoryEditor) {
			$categoryEditor->update(array(
				'isDisabled' => 1 - $categoryEditor->isDisabled
			));
		}
	}
	
	/**
	 * @see	wcf\data\ICollapsibleContainerAction::toggleContainer()
	 */
	public function toggleContainer() {
		$collapsibleObjectTypeName = $this->objects[0]->getCategoryType()->getObjectTypeName('com.woltlab.wcf.collapsibleContent');
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
	 * @see	wcf\data\IPositionAction::updatePosition()
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
					'parentCategoryID' => $parentCategoryID ? $this->objects[$parentCategoryID]->categoryID : 0,
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
			if (!$categoryEditor->getCategoryType()->canDeleteCategory()) {
				throw new ValidateActionException('Insufficient permissions');
			}
		}
	}
	
	/**
	 * @see	wcf\data\IToggleAction::validateToggle()
	 */
	public function validateToggle() {
		$this->validateUpdate();
	}
	
	/**
	 * @see	wcf\data\ICollapsibleContainerAction::validateToggleContainer()
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
	 * @see	wcf\data\IPositionAction::validateUpdatePosition()
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
				$category = CategoryHandler::getInstance()->getCategory($parentCategoryID);
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
				$category = CategoryHandler::getInstance()->getCategory($categoryID);
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
