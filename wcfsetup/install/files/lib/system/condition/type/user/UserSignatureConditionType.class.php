<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\system\condition\type\IMigrateConditionType;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UserSignatureConditionType extends AbstractUserBooleanConditionType implements IMigrateConditionType
{
    public function __construct()
    {
        parent::__construct('signature', 'signature');
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        if ($this->filter) {
            $objectList->getConditionBuilder()->add(
                "({$objectList->getDatabaseTableAlias()}.signature <> ? AND {$objectList->getDatabaseTableAlias()}.signature IS NOT NULL)",
                ['']
            );
        } else {
            $objectList->getConditionBuilder()->add(
                "({$objectList->getDatabaseTableAlias()}.signature = ? OR {$objectList->getDatabaseTableAlias()}.signature IS NULL)",
                ['']
            );
        }
    }

    #[\Override]
    public function matches(object $object): bool
    {
        if ($this->filter) {
            return $object->signature !== '' && $object->signature !== null;
        } else {
            return $object->signature === '' || $object->signature === null;
        }
    }

    public function canMigrateConditionData(string $objectType): bool
    {
        return $objectType === 'com.woltlab.wcf.signature';
    }

    public function migrateConditionData(array &$conditionData): array
    {
        if (!isset($conditionData['userSignature'])) {
            return [];
        }

        $result = [
            [
                'identifier' => $this->getIdentifier(),
                'value' => $conditionData['userSignature'] === 1,
            ],
        ];

        unset($conditionData['userSignature']);

        return $result;
    }
}
