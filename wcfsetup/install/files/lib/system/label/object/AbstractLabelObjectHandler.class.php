<?php
namespace wcf\system\label\object;
use wcf\system\exception\SystemException;
use wcf\system\label\LabelHandler;
use wcf\system\SingletonFactory;

/**
 * Abstract implementation of a label object handler.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.label.object
 * @category	Community Framework
 */
abstract class AbstractLabelObjectHandler extends SingletonFactory implements ILabelObjectHandler {
	/**
	 * list of available label groups
	 * @var	array<\wcf\data\label\group\ViewableLabelGroup>
	 */
	protected $labelGroups = array();
	
	/**
	 * object type name
	 * @var	string
	 */
	protected $objectType = '';
	
	/**
	 * object type id
	 * @var	integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->labelGroups = LabelHandler::getInstance()->getLabelGroups();
		
		$objectType = LabelHandler::getInstance()->getObjectType($this->objectType);
		if ($objectType === null) {
			throw new SystemException("object type '".$this->objectType."' is invalid");
		}
		$this->objectTypeID = $objectType->objectTypeID;
	}
	
	/**
	 * @see	\wcf\system\label\manager\ILabelObjectHandler::getLabelGroupIDs()
	 */
	public function getLabelGroupIDs(array $parameters = array()) {
		return array_keys($this->labelGroups);
	}
	
	/**
	 * @see	\wcf\system\label\manager\ILabelObjectHandler::getLabelGroups()
	 */
	public function getLabelGroups(array $parameters = array()) {
		$groupIDs = $this->getLabelGroupIDs($parameters);
		
		$data = array();
		foreach ($groupIDs as $groupID) {
			$data[$groupID] = $this->labelGroups[$groupID];
		}
		
		uasort($data, array('\wcf\data\label\group\LabelGroup', 'sortLabelGroups'));
		
		return $data;
	}
	
	/**
	 * @see	\wcf\system\label\manager\ILabelObjectHandler::validateLabelIDs()
	 */
	public function validateLabelIDs(array $labelIDs, $optionName = '', $legacyReturnValue = true) {
		$optionID = 0;
		if (!empty($optionName)) {
			$optionID = LabelHandler::getInstance()->getOptionID($optionName);
			if ($optionID === null) {
				throw new SystemException("Cannot validate label permissions, option '".$optionName."' is unknown");
			}
		}
		
		$validationErrors = array();
		$satisfiedGroups = array();
		foreach ($labelIDs as $groupID => $labelID) {
			// only one label per group is allowed
			if (is_array($labelID)) {
				$validationErrors[$groupID] = 'invalid';
				continue;
			}
			
			// label group id is unknown
			if (!isset($this->labelGroups[$groupID])) {
				$validationErrors[0] = 'invalid';
				continue;
			}
			
			// label id is invalid for this group
			if (!$this->labelGroups[$groupID]->isValid($labelID)) {
				$validationErrors[$groupID] = 'invalid';
				continue;
			}
			
			// check permission
			if ($optionID && !$this->labelGroups[$groupID]->getPermission($optionID)) {
				$validationErrors[0] = 'invalid';
				continue;
			}
			
			$satisfiedGroups[] = $groupID;
		}
		
		// check if required label groups were set
		foreach ($this->labelGroups as $labelGroup) {
			if ($labelGroup->forceSelection && !in_array($labelGroup->groupID, $satisfiedGroups)) {
				// check if group wasn't set, but is not accessible for this user anyway
				if (!$labelGroup->getPermission($optionID)) {
					continue;
				}
				
				$validationErrors[$labelGroup->groupID] = 'missing';
				continue;
			}
		}
		
		if ($legacyReturnValue) {
			// WCF 2.0: boolean true/false
			return (empty($validationErrors));
		}
		else {
			// WCF 2.1+: detailed error report (empty array if validation passed)
			return $validationErrors;
		}
	}
	
	/**
	 * @see	\wcf\system\label\manager\ILabelObjectHandler::setLabels()
	 */
	public function setLabels(array $labelIDs, $objectID, $validatePermissions = true) {
		LabelHandler::getInstance()->setLabels($labelIDs, $this->objectTypeID, $objectID, $validatePermissions);
	}
	
	/**
	 * @see	\wcf\system\label\manager\ILabelObjectHandler::removeLabels()
	 */
	public function removeLabels($objectID, $validatePermissions = true) {
		LabelHandler::getInstance()->removeLabels($this->objectTypeID, $objectID, $validatePermissions);
	}
	
	/**
	 * @see	\wcf\system\label\manager\ILabelObjectHandler::getAssignedLabels()
	 */
	public function getAssignedLabels(array $objectIDs, $validatePermissions = true) {
		return LabelHandler::getInstance()->getAssignedLabels($this->objectTypeID, $objectIDs, $validatePermissions);
	}
}
