<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\object\filter\IObjectListFilter;
use wcf\system\WCF;

/**
 * Filters users that are not a member of the user group with the given id.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @implements IObjectListFilter<User, int>
 */
final class UserNotInGroupObjectFilter implements IObjectListFilter
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
    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.objectFilter.user.notInGroup');
    }

    #[\Override]
    public function getFormField(): SelectFormField
    {
        return SelectFormField::create('userNotInGroup')
            ->label('wcf.objectFilter.user.group.group')
            ->options(UserGroupObjectFilter::getSelectableGroups($this->includeGuests), labelLanguageItems: false)
            ->required();
    }

    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return (string)$value;
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): int
    {
        return (int)$serializedValue;
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        return WCF::getLanguage()->getDynamicVariable('wcf.objectFilter.user.notInGroup.summary', [
            'group' => UserGroup::getGroupByID($value)?->getName() ?? $value,
        ]);
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
