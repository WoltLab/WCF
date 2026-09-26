<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyCache;
use wcf\data\user\trophy\UserTrophyList;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\object\filter\AbstractSelectionObjectFilter;
use wcf\system\object\filter\IObjectListFilter;
use wcf\system\WCF;

/**
 * Filters users that have received the trophy with the given id. Guests never match.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractSelectionObjectFilter<User>
 * @implements IObjectListFilter<User, int>
 */
final class UserTrophyObjectFilter extends AbstractSelectionObjectFilter implements IObjectListFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userTrophy';
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.user.trophy';
    }

    #[\Override]
    protected function createOptions(): array
    {
        return UserTrophyObjectFilter::getSelectableTrophies();
    }

    #[\Override]
    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void
    {
        $conditions->add(
            'user_table.userID IN (
                SELECT  userID
                FROM    wcf1_user_trophy
                WHERE   trophyID = ?
            )',
            [$value]
        );
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        return \in_array($configuredValue, UserTrophyObjectFilter::getTrophyIDs($object), true);
    }

    /**
     * Returns the titles of the trophies that can be selected, indexed by their id.
     *
     * @return array<int, string>
     * @internal
     */
    public static function getSelectableTrophies(): array
    {
        $trophies = \array_map(
            static fn(Trophy $trophy) => $trophy->getTitle(),
            TrophyCache::getInstance()->getTrophies(),
        );

        $collator = new \Collator(WCF::getLanguage()->getLocale());
        \uasort(
            $trophies,
            static fn(string $a, string $b) => $collator->compare($a, $b)
        );

        return $trophies;
    }

    /**
     * Returns the ids of the enabled trophies the given user has received.
     *
     * @return list<int>
     * @internal
     */
    public static function getTrophyIDs(User $user): array
    {
        if ($user->isGuest()) {
            return [];
        }

        $userTrophies = UserTrophyList::getUserTrophies([$user->userID])[$user->userID];

        return \array_values(\array_map(
            static fn($userTrophy) => $userTrophy->trophyID,
            $userTrophies,
        ));
    }
}
