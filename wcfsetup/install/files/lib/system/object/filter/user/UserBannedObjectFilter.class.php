<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\object\filter\IObjectListFilter;
use wcf\system\WCF;

/**
 * Filters users by whether they are banned. Guests never match.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @implements IObjectListFilter<User, bool>
 */
final class UserBannedObjectFilter implements IObjectListFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userBanned';
    }

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.objectFilter.user.banned');
    }

    #[\Override]
    public function getFormField(): BooleanFormField
    {
        return BooleanFormField::create('userBanned')
            ->label('wcf.objectFilter.user.banned');
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
            return WCF::getLanguage()->get('wcf.objectFilter.user.banned.summary.yes');
        }

        return WCF::getLanguage()->get('wcf.objectFilter.user.banned.summary.no');
    }

    #[\Override]
    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void
    {
        if ($value) {
            $conditions->add('banned = 1');
        } else {
            $conditions->add('banned = 0');
        }
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        if ($object->isGuest()) {
            return false;
        }

        return $configuredValue === ($object->banned !== 0);
    }
}
