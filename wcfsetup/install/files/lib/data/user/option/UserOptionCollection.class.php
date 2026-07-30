<?php

namespace wcf\data\user\option;

use wcf\data\DatabaseObject;
use wcf\data\option\OptionCollection;
use wcf\system\l10n\L10nStorage;

/**
 * Collection of user options that batch-loads their localized values from the
 * `wcf1_user_option_l10n` table.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class UserOptionCollection extends OptionCollection
{
    /**
     * @var array<int, array<string, array<int, string>>>
     */
    private array $l10nValues;

    public function getResolvedL10nValue(DatabaseObject $object, string $columnName): string
    {
        $this->loadL10nValues();

        return L10nStorage::resolveValue($this->l10nValues[$object->getObjectID()][$columnName] ?? []);
    }

    /**
     * @return array<int, string>
     */
    public function getL10nValues(DatabaseObject $object, string $columnName): array
    {
        $this->loadL10nValues();

        return $this->l10nValues[$object->getObjectID()][$columnName] ?? [];
    }

    private function loadL10nValues(): void
    {
        if (isset($this->l10nValues)) {
            return;
        }

        $objectIDs = $this->getObjectIDs();
        $this->l10nValues = $objectIDs === []
            ? []
            : (new L10nStorage(UserOption::getL10nDefinition()))->getValuesForObjects($objectIDs);
    }
}
