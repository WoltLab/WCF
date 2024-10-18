<?php

namespace wcf\system\importer;

use wcf\data\label\group\LabelGroup;
use wcf\data\label\group\LabelGroupEditor;
use wcf\system\WCF;

/**
 * Imports label groups.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LabelGroupImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = LabelGroup::class;

    /**
     * @inheritDoc
     */
    public function import($oldID, array $data, array $additionalData = [])
    {
        // save label group
        $labelGroup = LabelGroupEditor::create($data);

        // save objects
        if (!empty($additionalData['objects'])) {
            $sql = "INSERT INTO wcf1_label_group_to_object
                                (groupID, objectTypeID, objectID)
                    VALUES      (?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);

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
