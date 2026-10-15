<?php

namespace wcf\system\importer;

use wcf\command\file\CreateFileFromExistingFile;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyBuilder;
use wcf\system\l10n\L10nStorage;
use wcf\system\WCF;

/**
 * Represents a trophy importer.
 *
 * @author  Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        if (isset($data['categoryID'])) {
            $data['categoryID'] = ImportHandler::getInstance()
                ->getNewID('com.woltlab.wcf.trophy.category', $data['categoryID']);
        }

        if (empty($data['categoryID'])) {
            $data['categoryID'] = $this->getImportCategoryID();
        }

        $type = (int)$data['type'];
        $builder = TrophyBuilder::forCreate()
            ->setCategoryID((int)$data['categoryID'])
            ->setType($type)
            ->setTitle($this->getL10nValues('title', $data, $additionalData))
            ->setDescription($this->getL10nValues('description', $data, $additionalData));

        if ($type === Trophy::TYPE_IMAGE) {
            if (!isset($additionalData['fileLocation']) || !@\file_exists($additionalData['fileLocation'])) {
                return 0;
            }

            $file = new CreateFileFromExistingFile(
                $additionalData['fileLocation'],
                \basename($additionalData['fileLocation']),
                'com.woltlab.wcf.trophy.image',
                copy: true,
            )();
            if ($file === null) {
                return 0;
            }

            $builder->setIconFileID($file->fileID);
        }

        // The show order is imported as is to keep the order of the other trophies.
        $handledColumns = ['trophyID', 'categoryID', 'type', 'title', 'description', 'iconFile', 'iconFileID'];
        foreach ($data as $key => $value) {
            if (\in_array($key, $handledColumns, true)) {
                continue;
            }
            if ($value !== null && !\is_string($value) && !\is_int($value) && !\is_float($value)) {
                continue;
            }

            $builder->setCustomProperty($key, $value);
        }

        $trophy = $builder->create();

        ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.trophy', $oldID, $trophy->trophyID);

        return $trophy->trophyID;
    }

    /**
     * Returns the localized values of the given property, which are either
     * provided per language or as a monolingual value.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $additionalData
     * @return array<int, string>
     */
    private function getL10nValues(string $property, array $data, array $additionalData): array
    {
        if (!empty($additionalData['i18n'][$property]) && \is_array($additionalData['i18n'][$property])) {
            $values = [];
            foreach ($additionalData['i18n'][$property] as $languageID => $value) {
                $values[(int)$languageID] = (string)$value;
            }

            return $values;
        }

        return [L10nStorage::MONOLINGUAL => (string)($data[$property] ?? '')];
    }

    /**
     * Returns a categoryID for trophies without categoryID.
     *
     * @return int
     */
    private function getImportCategoryID()
    {
        if ($this->importCategoryID === 0) {
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
            if ($categoryID !== false) {
                $this->importCategoryID = $categoryID;
            } else {
                $sql = "INSERT INTO wcf1_category
                                    (objectTypeID, parentCategoryID, title, showOrder, time)
                        VALUES      (?, ?, ?, ?, ?)";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$objectTypeID, 0, 'Import', 0, \TIME_NOW]);
                $this->importCategoryID = (int)WCF::getDB()->getInsertID("wcf1_category", 'categoryID');
            }
        }

        return $this->importCategoryID;
    }
}
