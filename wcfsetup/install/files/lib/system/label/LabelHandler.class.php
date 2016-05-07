<?php
namespace wcf\system\label;
use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\LabelCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages labels and label-to-object associations.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.label
 * @category	Community Framework
 */
class LabelHandler extends SingletonFactory {
	/**
	 * cached list of object types
	 * @var	mixed[][]
	 */
	protected $cache = null;
	
	/**
	 * list of label groups
	 * @var	ViewableLabelGroup[]
	 */
	protected $labelGroups = null;
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->cache = array(
			'objectTypes' => array(),
			'objectTypeNames' => array()
		);
		
		$cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.label.object');
		foreach ($cache as $objectType) {
			$this->cache['objectTypes'][$objectType->objectTypeID] = $objectType;
			$this->cache['objectTypeNames'][$objectType->objectType] = $objectType->objectTypeID;
		}
		
		$this->labelGroups = LabelCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Returns the id of the label ACL option with the given name or null if
	 * no such option exists.
	 * 
	 * @param	string		$optionName
	 * @return	integer
	 */
	public function getOptionID($optionName) {
		foreach ($this->labelGroups['options'] as $option) {
			if ($option->optionName == $optionName) {
				return $option->optionID;
			}
		}
		
		return null;
	}
	
	/**
	 * Returns the label object type with the given name or null of no such
	 * object.
	 * 
	 * @param	string		$objectType
	 * @return	\wcf\data\object\type\ObjectType
	 */
	public function getObjectType($objectType) {
		if (isset($this->cache['objectTypeNames'][$objectType])) {
			$objectTypeID = $this->cache['objectTypeNames'][$objectType];
			return $this->cache['objectTypes'][$objectTypeID];
		}
		
		return null;
	}
	
	/**
	 * Returns an array with view permissions for the labels with the given id.
	 * 
	 * @param	integer[]		$labelIDs
	 * @return	array
	 * @see		\wcf\system\label\LabelHandler::getPermissions()
	 */
	public function validateCanView(array $labelIDs) {
		return $this->getPermissions('canViewLabel', $labelIDs);
	}
	
	/**
	 * Returns an array with use permissions for the labels with the given id.
	 * 
	 * @param	integer[]		$labelIDs
	 * @return	array
	 * @see		\wcf\system\label\LabelHandler::getPermissions()
	 */
	public function validateCanUse(array $labelIDs) {
		return $this->getPermissions('canUseLabel', $labelIDs);
	}
	
	/**
	 * Returns an array with boolean values for each given label id.
	 * 
	 * @param	string			$optionName
	 * @param	integer[]		$labelIDs
	 * @return	array
	 * @throws	SystemException
	 */
	public function getPermissions($optionName, array $labelIDs) {
		if (empty($labelIDs)) {
			// nothing to validate anyway
			return array();
		}
		
		if (empty($this->labelGroups['groups'])) {
			// pretend given label ids aren't valid
			$data = array();
			foreach ($labelIDs as $labelID) $data[$labelID] = false;
			
			return $data;
		}
		
		$optionID = $this->getOptionID($optionName);
		if ($optionID === null) {
			throw new SystemException("cannot validate label ids, ACL options missing");
		}
		
		// validate each label
		$data = array();
		foreach ($labelIDs as $labelID) {
			$isValid = false;
			
			foreach ($this->labelGroups['groups'] as $group) {
				if (!$group->isValid($labelID)) {
					continue;
				}
				
				if ($group->getPermission($optionID)) {
					$isValid = true;
				}
			}
			
			$data[$labelID] = $isValid;
		}
		
		return $data;
	}
	
	/**
	 * Sets labels for given object id, pass an empty array to remove all previously
	 * assigned labels.
	 * 
	 * @param	integer[]		$labelIDs
	 * @param	integer			$objectTypeID
	 * @param	integer			$objectID
	 * @param	boolean			$validatePermissions
	 */
	public function setLabels(array $labelIDs, $objectTypeID, $objectID, $validatePermissions = true) {
		// get accessible label ids to prevent unaccessible ones to be removed
		$accessibleLabelIDs = $this->getAccessibleLabelIDs();
		
		// delete previous labels
		$conditions = new PreparedStatementConditionBuilder();
		if ($validatePermissions) $conditions->add("labelID IN (?)", array($accessibleLabelIDs));
		$conditions->add("objectTypeID = ?", array($objectTypeID));
		$conditions->add("objectID = ?", array($objectID));
		
		if (!$validatePermissions || ($validatePermissions && !empty($accessibleLabelIDs))) {
			$sql = "DELETE FROM	wcf".WCF_N."_label_object
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
		}
		
		// insert new labels
		if (!empty($labelIDs)) {
			$sql = "INSERT INTO	wcf".WCF_N."_label_object
						(labelID, objectTypeID, objectID)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($labelIDs as $labelID) {
				$statement->execute(array(
					$labelID,
					$objectTypeID,
					$objectID
				));
			}
		}
	}
	
	/**
	 * Returns all assigned labels, optionally filtered to validate permissions.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer[]	$objectIDs
	 * @param	boolean		$validatePermissions
	 * @return	array
	 */
	public function getAssignedLabels($objectTypeID, array $objectIDs, $validatePermissions = true) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", array($objectTypeID));
		$conditions->add("objectID IN (?)", array($objectIDs));
		$sql = "SELECT	objectID, labelID
			FROM	wcf".WCF_N."_label_object
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$labels = array();
		while ($row = $statement->fetchArray()) {
			if (!isset($labels[$row['labelID']])) {
				$labels[$row['labelID']] = array();
			}
			
			$labels[$row['labelID']][] = $row['objectID'];
		}
		
		// optionally filter out labels without permissions
		if ($validatePermissions) {
			$labelIDs = array_keys($labels);
			$result = $this->validateCanView($labelIDs);
			
			foreach ($labelIDs as $labelID) {
				if (!$result[$labelID]) {
					unset($labels[$labelID]);
				}
			}
		}
		
		// reorder the array by object id
		$data = array();
		foreach ($labels as $labelID => $objectIDs) {
			foreach ($objectIDs as $objectID) {
				if (!isset($data[$objectID])) {
					$data[$objectID] = array();
				}
				
				foreach ($this->labelGroups['groups'] as $group) {
					$label = $group->getLabel($labelID);
					if ($label !== null) {
						$data[$objectID][$labelID] = $label;
					}
				}
			}
		}
		
		// order label ids by label group
		$labelGroups =& $this->labelGroups;
		foreach ($data as &$labels) {
			uasort($labels, function($a, $b) use($labelGroups) {
				$groupA = $labelGroups['groups'][$a->groupID];
				$groupB = $labelGroups['groups'][$b->groupID];
				
				if ($groupA->showOrder == $groupB->showOrder) {
					return ($groupA->groupID > $groupB->groupID) ? 1 : -1;
				}
				
				return ($groupA->showOrder > $groupB->showOrder) ? 1 : -1;
			});
		}
		unset($labels);
		
		return $data;
	}
	
	/**
	 * Returns given label groups by id.
	 * 
	 * @param	integer[]	$groupIDs
	 * @param	boolean		$validatePermissions
	 * @param	string		$permission
	 * @return	ViewableLabelGroup[]
	 * @throws	SystemException
	 */
	public function getLabelGroups(array $groupIDs = array(), $validatePermissions = true, $permission = 'canSetLabel') {
		$data = array();
		
		if ($validatePermissions) {
			$optionID = $this->getOptionID($permission);
			if ($optionID === null) {
				throw new SystemException("cannot validate group ids, ACL options missing");
			}
		}
		
		if (empty($groupIDs)) $groupIDs = array_keys($this->labelGroups['groups']);
		foreach ($groupIDs as $groupID) {
			// validate given group ids
			if (!isset($this->labelGroups['groups'][$groupID])) {
				throw new SystemException("unknown label group identified by group id '".$groupID."'");
			}
			
			// validate permissions
			if ($validatePermissions) {
				if (!$this->labelGroups['groups'][$groupID]->getPermission($optionID)) {
					continue;
				}
			}
			
			$data[$groupID] = $this->labelGroups['groups'][$groupID];
		}
		
		uasort($data, array('\wcf\data\label\group\LabelGroup', 'sortLabelGroups'));
		
		return $data;
	}
	
	/**
	 * Returns a list of accessible label ids.
	 * 
	 * @return	integer[]
	 */
	public function getAccessibleLabelIDs() {
		$labelIDs = array();
		$groups = $this->getLabelGroups();
		
		foreach ($groups as $group) {
			$labelIDs = array_merge($labelIDs, $group->getLabelIDs());
		}
		
		return $labelIDs;
	}
	
	/**
	 * Returns label group by id.
	 * 
	 * @param	integer		$groupID
	 * @return	\wcf\data\label\group\ViewableLabelGroup
	 */
	public function getLabelGroup($groupID) {
		if (isset($this->labelGroups['groups'][$groupID])) {
			return $this->labelGroups['groups'][$groupID];
		}
		
		return null;
	}
	
	/**
	 * Removes all assigned labels for given object ids.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer[]	$objectIDs
	 */
	public function removeLabels($objectTypeID, array $objectIDs) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", array($objectTypeID));
		$conditions->add("objectID IN (?)", array($objectIDs));
		$sql = "DELETE FROM	wcf".WCF_N."_label_object
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
	}
}
