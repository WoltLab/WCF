<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\object\filter\AbstractBooleanObjectFilter;
use wcf\system\object\filter\IObjectListFilter;

/**
 * Filters users by whether they have an avatar. Guests never match.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractBooleanObjectFilter<User>
 * @implements IObjectListFilter<User, bool>
 */
final class UserAvatarObjectFilter extends AbstractBooleanObjectFilter implements IObjectListFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userAvatar';
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.user.avatar';
    }

    #[\Override]
    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void
    {
        if ($value) {
            $conditions->add("avatarFileID IS NOT NULL");
        } else {
            $conditions->add("avatarFileID IS NULL");
        }
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        if ($object->isGuest()) {
            return false;
        }

        return match ($configuredValue) {
            true => $object->avatarFileID !== null,
            false => $object->avatarFileID === null,
        };
    }
}
