<?php
namespace wcf\system\box;
use wcf\data\box\Box;
use wcf\data\box\BoxList;
use wcf\data\condition\ConditionAction;
use wcf\system\exception\SystemException;
use wcf\system\request\RequestHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles boxes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 * @since	2.2
 */
class BoxHandler extends SingletonFactory {
	/**
	 * boxes grouped by position
	 * @var	Box[][]
	 */
	protected $boxes = [];
	
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
		if ($pageID) $boxList->getConditionBuilder()->add('(box.visibleEverywhere = ? AND boxID NOT IN (SELECT boxID FROM wcf'.WCF_N.'_box_to_page WHERE pageID = ? AND visible = ?)) OR boxID IN (SELECT boxID FROM wcf'.WCF_N.'_box_to_page WHERE pageID = ? AND visible = ?)', array(1, $pageID, 0, $pageID, 1));
		else $boxList->getConditionBuilder()->add('box.visibleEverywhere = ?', array(1));
		$boxList->sqlOrderBy = 'showOrder';
		$boxList->readObjects();
		foreach ($boxList as $box) {
			if (!isset($this->boxes[$box->position])) $this->boxes[$box->position] = [];
			$this->boxes[$box->position][] = $box;
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
	 * @throws	SystemException
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
			throw new SystemException("Unknown box condition '{$conditionObjectType}' of condition definition '{$conditionDefinition}'");
		}
		
		$box = Box::getBoxByIdentifier($boxIdentifier);
		if ($box === null) {
			throw new SystemException("Unknown box with idenifier '{$boxIdentifier}'");
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
	 * Returns boxes for the given position.
	 * 
	 * @param	string		$position
	 * @return	Box[]
	 */
	public function getBoxes($position) {
		if (isset($this->boxes[$position])) {
			return $this->boxes[$position];
		}
		
		return [];
	}
}
