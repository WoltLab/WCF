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
        if ($value) {
            return '1';
        }

        return '0';
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
    public function testValue(mixed $configuredValue, mixed $value): bool
    {
        return (bool)$value;
    }
}
