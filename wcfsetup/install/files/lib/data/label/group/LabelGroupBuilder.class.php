<?php

namespace wcf\data\label\group;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\label\LabelAction;
use wcf\system\acl\ACLHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\l10n\L10nStorage;
use wcf\system\WCF;

/**
 * Builder for creating and updating label groups.
 *
 * The localized title is stored in the `wcf1_label_group_l10n` table (see
 * `L10nStorage`).
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<LabelGroup>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
final class LabelGroupBuilder extends DatabaseObjectBuilder
{
    /**
     * @var L10nValue
     */
    private array $groupName;

    /**
     * @param L10nValue $groupName
     */
    public function setGroupName(array $groupName): static
    {
        $this->groupName = $groupName;

        return $this;
    }

    public function setGroupDescription(string $groupDescription): static
    {
        $this->properties['groupDescription'] = $groupDescription;

        return $this;
    }

    public function setForceSelection(bool $forceSelection): static
    {
        $this->properties['forceSelection'] = $forceSelection ? 1 : 0;

        return $this;
    }

    public function setSortAlphabetically(bool $sortAlphabetically): static
    {
        $this->properties['sortAlphabetically'] = $sortAlphabetically ? 1 : 0;

        return $this;
    }

    public function setShowOrder(int $showOrder): static
    {
        $this->properties['showOrder'] = $showOrder;

        return $this;
    }

    #[\Override]
    protected function allowEmptyCreate(): bool
    {
        return true;
    }

    #[\Override]
    protected function afterValidateCreate(): void
    {
        if (!isset($this->groupName)) {
            throw new \BadMethodCallException("Missing value for 'groupName'.");
        }
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        $this->saveL10nValues($object);
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        if (isset($this->groupName)) {
            $this->saveL10nValues($object);
        }
    }

    #[\Override]
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        // The labels would be removed by the foreign key, but their phrases
        // would be left behind.
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add('groupID IN (?)', [$objectIDs]);

        $sql = "SELECT  labelID
                FROM    wcf1_label
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $labelIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if ($labelIDs !== []) {
            (new LabelAction($labelIDs, 'delete'))->executeAction();
        }

        ACLHandler::getInstance()->removeValues(
            ACLHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.label'),
            $objectIDs
        );
    }

    private function saveL10nValues(LabelGroup $group): void
    {
        (new L10nStorage(LabelGroup::getL10nDefinition()))->setValues(
            $group->groupID,
            [
                'groupName' => $this->groupName,
            ]
        );
    }
}
