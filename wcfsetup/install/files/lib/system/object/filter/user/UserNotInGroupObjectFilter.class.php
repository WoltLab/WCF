<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\object\filter\AbstractSelectionObjectFilter;
use wcf\system\object\filter\IObjectListFilter;

/**
 * Filters users that are not a member of the user group with the given id.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractSelectionObjectFilter<User>
 * @implements IObjectListFilter<User, int>
 */
final class UserNotInGroupObjectFilter extends AbstractSelectionObjectFilter implements IObjectListFilter
{
    /**
     * @param bool $includeGuests offers the guest group for selection, only useful if guests are tested
     */
    public function __construct(
        private readonly bool $includeGuests = false,
    ) {}

    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userNotInGroup';
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.user.notInGroup';
    }

    #[\Override]
    protected function createOptions(): array
    {
        return UserGroupObjectFilter::getSelectableGroups($this->includeGuests);
    }

    #[\Override]
    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void
    {
        $conditions->add(
            'user_table.userID NOT IN (
                SELECT  userID
                FROM    wcf1_user_to_group
                WHERE   groupID = ?
            )',
            [$value]
        );
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        return !\in_array($configuredValue, $object->getGroupIDs(), true);
    }
}
