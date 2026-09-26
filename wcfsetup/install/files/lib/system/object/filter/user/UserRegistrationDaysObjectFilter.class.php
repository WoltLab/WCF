<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\NumericRangeFormField;
use wcf\system\object\filter\AbstractRangeObjectFilter;
use wcf\system\object\filter\IObjectListFilter;

/**
 * Filters users by the number of days since their registration. The value
 * consists of an optional minimum and an optional maximum number of days,
 * both inclusive. Guests never match.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractRangeObjectFilter<User, int>
 * @implements IObjectListFilter<User, array{0: ?int, 1: ?int}>
 */
final class UserRegistrationDaysObjectFilter extends AbstractRangeObjectFilter implements IObjectListFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userRegistrationDays';
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.user.registrationDays';
    }

    #[\Override]
    protected function createFormField(): NumericRangeFormField
    {
        return NumericRangeFormField::create('userRegistrationDays')
            ->label('wcf.user.condition.registrationDateInterval')
            ->integerValues()
            ->minimum(0);
    }

    #[\Override]
    protected function unserializeBound(string $bound): int
    {
        return (int)$bound;
    }

    #[\Override]
    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void
    {
        [$from, $to] = $value;

        if ($from !== null) {
            $conditions->add('registrationDate <= ?', [\TIME_NOW - $from * 86400]);
        }
        if ($to !== null) {
            $conditions->add('registrationDate >= ?', [\TIME_NOW - $to * 86400]);
        }
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        if ($object->isGuest()) {
            return false;
        }

        [$from, $to] = $configuredValue;

        if ($from !== null && $object->registrationDate > \TIME_NOW - $from * 86400) {
            return false;
        }
        if ($to !== null && $object->registrationDate < \TIME_NOW - $to * 86400) {
            return false;
        }

        return true;
    }
}
