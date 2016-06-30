<?php
namespace wcf\system\box;
use wcf\data\box\Box;
use wcf\data\box\BoxList;
use wcf\data\condition\ConditionAction;
use wcf\data\page\Page;
use wcf\system\exception\SystemException;
use wcf\system\request\RequestHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles boxes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 * @since	3.0
 */
class BoxHandler extends SingletonFactory {
	/**
	 * boxes with box id as key
	 * @var	Box[]
	 */
	protected $boxes = [];
	
	/**
	 * identifier to boxes
	 * @var	Box[]
	 */
	protected $boxesByIdentifier = [];
	
	/**
	 * boxes grouped by position
	 * @var	Box[][]
	 */
	protected $boxesByPosition = [];
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		// get active page id
		$pageID = 0;
		if (($request = RequestHandler::getInstance()->getActiveRequest()) !== null) {
			$pageID = $request->getPageID();
		}
		
		// load box layout for active page
		$boxList = new BoxList();
		if ($pageID) $boxList->getConditionBuilder()->add('(box.visibleEverywhere = ? AND boxID NOT IN (SELECT boxID FROM wcf'.WCF_N.'_box_to_page WHERE pageID = ? AND visible = ?)) OR boxID IN (SELECT boxID FROM wcf'.WCF_N.'_box_to_page WHERE pageID = ? AND visible = ?)', [1, $pageID, 0, $pageID, 1]);
		else $boxList->getConditionBuilder()->add('box.visibleEverywhere = ?', [1]);
		$boxList->sqlOrderBy = 'showOrder';
		$boxList->readObjects();
		
		$this->boxes = $boxList->getObjects();
		foreach ($boxList as $box) {
			if ($box->isAccessible()) {
				if (!isset($this->boxesByPosition[$box->position])) $this->boxesByPosition[$box->position] = [];
				$this->boxesByPosition[$box->position][] = $box;
				
				$this->boxesByIdentifier[$box->identifier] = $box;
			}
		}
	}
	
	/**
	 * Creates a new condition for an existing box.
	 * 
	 * Note: The primary use of this method is to be used during package installation.
	 * 
	 * @param	string		$boxIdentifier
	 * @param	string		$conditionDefinition
	 * @param	string		$conditionObjectType
	 * @param	array		$conditionData
	 * @throws	\InvalidArgumentException
	 */
	public function createBoxCondition($boxIdentifier, $conditionDefinition, $conditionObjectType, array $conditionData) {
		// do not rely on caches during package installation
		$sql = "SELECT		objectTypeID
			FROM		wcf".WCF_N."_object_type object_type
			INNER JOIN	wcf".WCF_N."_object_type_definition object_type_definition
			ON		(object_type.definitionID = object_type_definition.definitionID)
			WHERE		objectType = ?
					AND definitionName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$conditionObjectType, $conditionDefinition]);
		$objectTypeID = $statement->fetchSingleColumn();
		
		if (!$objectTypeID) {
			throw new \InvalidArgumentException("Unknown box condition '{$conditionObjectType}' of condition definition '{$conditionDefinition}'");
		}
		
		$box = Box::getBoxByIdentifier($boxIdentifier);
		if ($box === null) {
			throw new \InvalidArgumentException("Unknown box with identifier '{$boxIdentifier}'");
		}
		
		(new ConditionAction([], 'create', [
			'data' => [
				'conditionData' => serialize($conditionData),
				'objectID' => $box->boxID,
				'objectTypeID' => $objectTypeID
			]
		]))->executeAction();
	}
	
	/**
	 * Returns the box with the given id or null.
	 * 
	 * @param	integer		$boxID
	 * @return	Box|null
	 */
	public function getBox($boxID) {
		if (isset($this->boxes[$boxID])) {
			return $this->boxes[$boxID];
		}
		
		return null;
	}
	
	/**
	 * Returns boxes for the given position.
	 * 
	 * @param	string		$position
	 * @return	Box[]
	 */
	public function getBoxes($position) {
		if (isset($this->boxesByPosition[$position])) {
			return $this->boxesByPosition[$position];
		}
		
		return [];
	}
	
	/**
	 * Returns the box with given identifier.
	 *
	 * @param	string		$identifier
	 * @return	Box|null
	 */
	public function getBoxByIdentifier($identifier) {
		if (isset($this->boxesByIdentifier[$identifier])) {
			return $this->boxesByIdentifier[$identifier];
		}
		
		return null;
	}
	
	/**
	 * Assigns pages to a certain box.
	 *
	 * Note: The primary use of this method is to be used during package installation.
	 *
	 * @param	string		$boxIdentifier
	 * @param	string[]	$pageIdentifiers
	 * @param	boolean		$visible
	 * @throws	\InvalidArgumentException
	 */
	public function addBoxToPageAssignments($boxIdentifier, array $pageIdentifiers, $visible = true) {
		$box = Box::getBoxByIdentifier($boxIdentifier);
		if ($box === null) {
			throw new \InvalidArgumentException("Unknown box with identifier '{$boxIdentifier}'");
		}
		
		$pages = [];
		foreach ($pageIdentifiers as $pageIdentifier) {
			$page = Page::getPageByIdentifier($pageIdentifier);
			if ($page === null) {
				throw new \InvalidArgumentException("Unknown page with identifier '{$pageIdentifier}'");
			}
			$pages[] = $page;
		}
		
		if (($visible && $box->visibleEverywhere) || (!$visible && !$box->visibleEverywhere)) {
			$sql = "DELETE FROM     wcf".WCF_N."_box_to_page
					WHERE           boxID = ?
							AND pageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($pages as $page) {
				$statement->execute([$box->boxID, $page->pageID]);
			}
		}
		else {
			$sql = "REPLACE INTO    wcf".WCF_N."_box_to_page
							(boxID, pageID, visible)
					VALUES          (?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($pages as $page) {
				$statement->execute([$box->boxID, $page->pageID, ($visible ? 1 : 0)]);
			}
		}
	}
}
