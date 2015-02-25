<?php
namespace wcf\system\importer;
use wcf\data\label\group\LabelGroupEditor;
use wcf\system\WCF;

/**
 * Imports label groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class LabelGroupImporter extends AbstractImporter {
	/**
	 * @see	\wcf\system\importer\AbstractImporter::$className
	 */
	protected $className = 'wcf\data\label\group\LabelGroup';
	
	/**
	 * @see	\wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		// save label group
		$labelGroup = LabelGroupEditor::create($data);
		
		// save objects
		if (!empty($additionalData['objects'])) {
			$sql = "INSERT INTO	wcf".WCF_N."_label_group_to_object
						(groupID, objectTypeID, objectID)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($additionalData['objects'] as $objectTypeID => $objectIDs) {
				foreach ($objectIDs as $objectID) {
					$statement->execute(array($labelGroup->groupID, $objectTypeID, $objectID));
				}
			}
		}
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.label.group', $oldID, $labelGroup->groupID);
		
		return $labelGroup->groupID;
	}
}
