<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\object\filter\IObjectListFilter;

/**
 * Filters users by whether they have an avatar.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @implements IObjectListFilter<User, bool>
 */
final class UserAvatarObjectFilter implements IObjectListFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userAvatar';
    }

    #[\Override]
    public function getTitle(): string
    {
        return 'TODO: user avatar';
    }

    #[\Override]
    public function getFormField(): BooleanFormField
    {
        return BooleanFormField::create('userAvatar')
            ->label('wcf.user.avatar');
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
    public function serializeValue(mixed $value): string
    {
        return $value ? '1' : '0';
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): bool
    {
        return (bool)$serializedValue;
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        if ($value) {
            return 'TODO: has an avatar';
        }

        return 'TODO: does not have an avatar';
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        return match ($configuredValue) {
            true => $object->avatarFileID !== null,
            false => $object->avatarFileID === null,
        };
    }
}
