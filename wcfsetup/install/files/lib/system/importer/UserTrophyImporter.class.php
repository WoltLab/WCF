<?php

namespace wcf\system\importer;

use wcf\data\user\trophy\UserTrophy;
use wcf\data\user\trophy\UserTrophyBuilder;
use wcf\system\l10n\L10nStorage;

/**
 * Represents a user trophy importer.
 *
 * @author  Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserTrophyImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = UserTrophy::class;

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        if (isset($data['trophyID'])) {
            $data['trophyID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.trophy', $data['trophyID']);
        }

        if (isset($data['userID'])) {
            $data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
        }

        if (empty($data['userID']) || empty($data['trophyID'])) {
            return 0;
        }

        $builder = UserTrophyBuilder::forCreate()
            ->setTrophyID((int)$data['trophyID'])
            ->setUserID((int)$data['userID'])
            ->setTime((int)($data['time'] ?? 0));

        $description = $this->getDescription($data, $additionalData);
        if ($description !== null) {
            $builder->setDescription($description);
        }

        $handledColumns = ['userTrophyID', 'trophyID', 'userID', 'time', 'description'];
        foreach ($data as $key => $value) {
            if (\in_array($key, $handledColumns, true)) {
                continue;
            }
            if ($value !== null && !\is_string($value) && !\is_int($value) && !\is_float($value)) {
                continue;
            }

            $builder->setCustomProperty($key, $value);
        }

        $userTrophy = $builder->create();

        ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.userTrophy', $oldID, $userTrophy->getObjectID());

        return $userTrophy->getObjectID();
    }

    /**
     * Returns the localized values of the custom description, which are either
     * provided per language or as a monolingual value, or `null` if there is
     * no custom description.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $additionalData
     * @return ?array<int, string>
     */
    private function getDescription(array $data, array $additionalData): ?array
    {
        $i18nValues = $additionalData['i18n']['description'] ?? null;
        if (\is_array($i18nValues) && $i18nValues !== []) {
            $values = [];
            foreach ($i18nValues as $languageID => $value) {
                $values[(int)$languageID] = (string)$value;
            }

            return $values;
        }

        $description = (string)($data['description'] ?? '');
        if ($description === '') {
            return null;
        }

        return [L10nStorage::MONOLINGUAL => $description];
    }
}
