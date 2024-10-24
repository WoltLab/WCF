<?php

namespace wcf\system\importer;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyEditor;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a trophy importer.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
class TrophyImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = Trophy::class;

    /**
     * category for orphaned trophies
     * @var int
     */
    private $importCategoryID = 0;

    /**
     * @inheritDoc
     */
    public function import($oldID, array $data, array $additionalData = [])
    {
        if (isset($data['categoryID'])) {
            $data['categoryID'] = ImportHandler::getInstance()
                ->getNewID('com.woltlab.wcf.trophy.category', $data['categoryID']);
        }

        if (!$data['categoryID']) {
            $data['categoryID'] = $this->getImportCategoryID();
        }

        if ($data['type'] == Trophy::TYPE_IMAGE) {
            if (!@\file_exists($additionalData['fileLocation'])) {
                return 0;
            }

            $filename = \basename($additionalData['fileLocation']);
            while (\file_exists(WCF_DIR . 'images/trophy/' . $filename)) {
                $filename = \substr(StringUtil::getRandomID(), 0, 5) . '_' . \basename($additionalData['fileLocation']);
            }

            if (!@\copy($additionalData['fileLocation'], WCF_DIR . 'images/trophy/' . $filename)) {
                return 0;
            }

            $data['iconFile'] = $filename;
        }

        $trophy = TrophyEditor::create($data);

        if (!empty($additionalData['i18n'])) {
            $values = [];

            foreach (['title', 'description'] as $property) {
                if (isset($additionalData['i18n'][$property])) {
                    $values[$property] = $additionalData['i18n'][$property];
                }
            }

            if (!empty($values)) {
                $updateData = [];
                if (isset($values['title'])) {
                    $updateData['title'] = 'wcf.user.trophy.title' . $trophy->trophyID;
                }
                if (isset($values['description'])) {
                    $updateData['description'] = 'wcf.user.trophy.description' . $trophy->trophyID;
                }

                $items = [];
                foreach ($values as $property => $propertyValues) {
                    foreach ($propertyValues as $languageID => $languageItemValue) {
                        $items[] = [
                            'languageID' => $languageID,
                            'languageItem' => 'wcf.user.trophy.' . $property . $trophy->trophyID,
                            'languageItemValue' => $languageItemValue,
                        ];
                    }
                }

                $this->importI18nValues($items, 'wcf.user.trophy', 'com.woltlab.wcf');

                (new TrophyEditor($trophy))->update($updateData);
            }
        }

        ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.trophy', $oldID, $trophy->trophyID);

        return $trophy->trophyID;
    }

    /**
     * Returns a categoryID for trophies without categoryID.
     *
     * @return int
     */
    private function getImportCategoryID()
    {
        if (!$this->importCategoryID) {
            $objectTypeID = ObjectTypeCache::getInstance()
                ->getObjectTypeIDByName('com.woltlab.wcf.category', 'com.woltlab.wcf.trophy.category');

            $sql = "SELECT      categoryID
                    FROM        wcf1_category
                    WHERE       objectTypeID = ?
                            AND parentCategoryID = ?
                            AND title = ?
                    ORDER BY    categoryID";
            $statement = WCF::getDB()->prepare($sql, 1);
            $statement->execute([$objectTypeID, 0, 'Import']);
            $categoryID = $statement->fetchSingleColumn();
            if ($categoryID) {
                $this->importCategoryID = $categoryID;
            } else {
                $sql = "INSERT INTO wcf1_category
                                    (objectTypeID, parentCategoryID, title, showOrder, time)
                        VALUES      (?, ?, ?, ?, ?)";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$objectTypeID, 0, 'Import', 0, TIME_NOW]);
                $this->importCategoryID = WCF::getDB()->getInsertID("wcf1_category", 'categoryID');
            }
        }

        return $this->importCategoryID;
    }
}
