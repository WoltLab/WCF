<?php

namespace wcf\system\importer;

use wcf\data\label\group\LabelGroup;
use wcf\data\label\group\LabelGroupBuilder;
use wcf\system\l10n\L10nStorage;
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

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        // save label group
        $builder = LabelGroupBuilder::forCreate()
            ->setGroupName([L10nStorage::MONOLINGUAL => (string)$data['groupName']]);

        $handledColumns = ['groupID', 'groupName'];
        foreach ($data as $key => $value) {
            if (\in_array($key, $handledColumns, true)) {
                continue;
            }
            if ($value !== null && !\is_string($value) && !\is_int($value) && !\is_float($value)) {
                continue;
            }

            $builder->setCustomProperty($key, $value);
        }

        $labelGroup = $builder->create();

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
