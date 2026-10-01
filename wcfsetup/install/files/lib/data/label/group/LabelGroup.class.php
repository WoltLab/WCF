<?php

namespace wcf\data\label\group;

use wcf\data\CollectionDatabaseObject;
use wcf\data\DatabaseObject;
use wcf\system\l10n\L10nDefinition;
use wcf\system\l10n\L10nStorage;
use wcf\system\request\IRouteController;

/**
 * Represents a label group.
 *
 * The localized title is stored in the `wcf1_label_group_l10n` table.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $groupID            unique id of the label group
 * @property-read   string  $groupDescription   description of the label group (only shown in ACP)
 * @property-read   0|1     $forceSelection     is `1` if a label in the label group has to be selected when creating an object for which the label group is available, otherwise `0`
 * @property-read   0|1     $sortAlphabetically is `1` if labels in the label group are sorted alphabetically by their translated name, otherwise `0`
 * @property-read   int     $showOrder          position of the label group in relation to the other label groups
 *
 * @extends CollectionDatabaseObject<LabelGroupCollection>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
class LabelGroup extends CollectionDatabaseObject implements IRouteController, \Stringable
{
    #[\Override]
    public function getTitle(): string
    {
        return $this->getCollection()->getResolvedL10nValue($this, 'groupName');
    }

    /**
     * Returns the localized values of the given column as a
     * `languageID => value` map (see `L10nStorage`).
     *
     * @return L10nValue
     * @since 6.3
     */
    public function getL10nValues(string $columnName): array
    {
        if ($columnName !== 'groupName') {
            throw new \InvalidArgumentException("Invalid column name given.");
        }

        return $this->getCollection()->getL10nValues($this, $columnName);
    }

    /**
     * Returns label group title.
     */
    #[\Override]
    public function __toString(): string
    {
        return $this->getTitle();
    }

    /**
     * Returns the title and, if available, the description as a combined string.
     *
     * @since 6.2
     */
    public function getExtendedTitle(): string
    {
        if ($this->groupDescription === '') {
            return $this->getTitle();
        }

        return \sprintf(
            "%s / %s",
            $this->getTitle(),
            $this->groupDescription
        );
    }

    /**
     * Callback for uasort() to sort label groups by show order and (if equal) group id.
     *
     * @param LabelGroup $groupA
     * @param LabelGroup $groupB
     * @return  int
     */
    public static function sortLabelGroups(DatabaseObject $groupA, DatabaseObject $groupB)
    {
        if ($groupA->showOrder === $groupB->showOrder) {
            return ($groupA->groupID > $groupB->groupID) ? 1 : -1;
        }

        return ($groupA->showOrder > $groupB->showOrder) ? 1 : -1;
    }

    /**
     * @since 6.3
     */
    public static function getL10nDefinition(): L10nDefinition
    {
        return new L10nDefinition(
            'wcf1_label_group',
            'wcf1_label_group_l10n',
            'groupID',
            ['groupName'],
            maximumLengths: ['groupName' => 80],
        );
    }
}
