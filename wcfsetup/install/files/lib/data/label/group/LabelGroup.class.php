<?php

namespace wcf\data\label\group;

use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a label group.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $groupID        unique id of the label group
 * @property-read   string $groupName      name of the label group or name of language item which contains the label text
 * @property-read   string $groupDescription   description of the label group (only shown in ACP)
 * @property-read   int $forceSelection     is `1` if a label in the label group has to be selected when creating an object for which the label group is available, otherwise `0`
 * @property-read   int $showOrder      position of the label group in relation to the other label groups
 */
class LabelGroup extends DatabaseObject implements IRouteController
{
    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->groupName);
    }

    /**
     * Returns label group title.
     */
    public function __toString(): string
    {
        return $this->getTitle();
    }

    /**
     * Callback for uasort() to sort label groups by show order and (if equal) group id.
     *
     * @param DatabaseObject $groupA
     * @param DatabaseObject $groupB
     * @return  int
     */
    public static function sortLabelGroups(DatabaseObject $groupA, DatabaseObject $groupB)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        if ($groupA->showOrder == $groupB->showOrder) {
            /** @noinspection PhpUndefinedFieldInspection */
            return ($groupA->groupID > $groupB->groupID) ? 1 : -1;
        }

        /** @noinspection PhpUndefinedFieldInspection */
        return ($groupA->showOrder > $groupB->showOrder) ? 1 : -1;
    }
}
