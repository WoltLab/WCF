<?php

namespace wcf\system\object\filter\user;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\object\filter\IObjectFilter;

/**
 * @implements IObjectFilter<bool>
 */
final class UserAvatarObjectFilter implements IObjectFilter
{
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userAvatar';
    }

    public function getTitle(): string
    {
        return 'TODO: user avatar';
    }

    public function getFormField(): BooleanFormField
    {
        return BooleanFormField::create('userAvatar')
            ->label('wcf.user.avatar');
    }

    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void
    {
        if ($value) {
            $conditions->add("avatarID IS NOT NULL");
        } else {
            $conditions->add("avatarID IS NULL");
        }
    }

    public function serializeValue(mixed $value): string
    {
        if ($value) {
            return '1';
        }

        return '0';
    }

    public function unserializeValue(string $serializedValue): bool
    {
        return (bool)$serializedValue;
    }

    public function summarizeValue(mixed $value): string
    {
        if ($value) {
            return 'TODO: has an avatar';
        }

        return 'TODO: does not have an avatar';
    }

    public function testValue(mixed $configuredValue, mixed $value): bool
    {
        return (bool)$value;
    }
}
