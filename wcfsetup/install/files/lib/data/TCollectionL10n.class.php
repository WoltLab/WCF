<?php

namespace wcf\data;

use wcf\system\l10n\L10nDefinition;
use wcf\system\l10n\L10nStorage;

/**
 * Trait for dbo collections that batch-load the localized values of their
 * objects from a `*_l10n` table.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
trait TCollectionL10n
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

        $this->l10nValues = (new L10nStorage($this->getL10nDefinition()))->getValuesForObjects(
            $this->getObjectIDs()
        );
    }

    abstract protected function getL10nDefinition(): L10nDefinition;
}
