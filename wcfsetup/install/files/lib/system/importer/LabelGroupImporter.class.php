<?php
namespace wcf\system\importer;
use wcf\data\label\group\LabelGroup;
use wcf\data\label\group\LabelGroupEditor;
use wcf\system\WCF;

/**
 * Imports label groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class LabelGroupImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = LabelGroup::class;
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
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
					$statement->execute([$labelGroup->groupID, $objectTypeID, $objectID]);
				}
			}
		}
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.label.group', $oldID, $labelGroup->groupID);
		
		return $labelGroup->groupID;
	}
}
