<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\object\filter\AbstractSelectionObjectFilter;
use wcf\system\object\filter\IObjectListFilter;

/**
 * Filters users that have not received the trophy with the given id.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractSelectionObjectFilter<User>
 * @implements IObjectListFilter<User, int>
 */
final class UserNoTrophyObjectFilter extends AbstractSelectionObjectFilter implements IObjectListFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userNoTrophy';
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.user.noTrophy';
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
            'user_table.userID NOT IN (
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
        return !\in_array($configuredValue, UserTrophyObjectFilter::getTrophyIDs($object), true);
    }
}
