<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyList;
use wcf\data\user\trophy\UserTrophyList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\WCF;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @implements IDatabaseObjectListConditionType<UserList<User>, int>
 * @implements IObjectConditionType<User, int>
 * @extends AbstractConditionType<int>
 */
final class UserHasTrophyConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType
{
    #[\Override]
    public function getFormField(string $id): SelectFormField
    {
        return SelectFormField::create($id)
            ->options($this->getTrophies())
            ->required();
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'hasTrophy';
    }

    #[\Override]
    public function getLabel(): string
    {
        return 'wcf.condition.user.hasTrophy';
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        $objectList->getConditionBuilder()->add(
            "{$objectList->getDatabaseTableAlias()}.userID IN (
                    SELECT userID
                    FROM   wcf1_user_trophy
                    WHERE  trophyID = ?
            )",
            [$this->filter]
        );
    }

    #[\Override]
    public function matches(object $object): bool
    {
        $userTrophies = UserTrophyList::getUserTrophies([$object->userID], false)[$object->userID];
        $trophyIDs = \array_column($userTrophies, 'trophyID');

        return \in_array($this->filter, $trophyIDs, true);
    }

    /**
     * @return Trophy[]
     */
    private function getTrophies(): array
    {
        $trophyList = new TrophyList();
        $trophyList->readObjects();
        $trophies = $trophyList->getObjects();

        $collator = new \Collator(WCF::getLanguage()->getLocale());
        \uasort(
            $trophies,
            static fn (Trophy $a, Trophy $b) => $collator->compare($a->getTitle(), $b->getTitle())
        );

        return $trophies;
    }
}
