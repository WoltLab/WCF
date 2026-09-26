<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\NumericRangeFormField;
use wcf\system\object\filter\IObjectListFilter;
use wcf\system\object\filter\TRangeObjectFilter;
use wcf\system\WCF;

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
 * @implements IObjectListFilter<User, array{0: ?int, 1: ?int}>
 */
final class UserRegistrationDaysObjectFilter implements IObjectListFilter
{
    use TRangeObjectFilter;

    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userRegistrationDays';
    }

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.objectFilter.user.registrationDays');
    }

    #[\Override]
    public function getFormField(): NumericRangeFormField
    {
        return NumericRangeFormField::create('userRegistrationDays')
            ->label('wcf.user.condition.registrationDateInterval')
            ->integerValues()
            ->minimum(0)
            ->addValidator($this->getRangeValidator());
    }

    /**
     * @param string|array{0: ?int, 1: ?int} $value
     */
    #[\Override]
    public function serializeValue(mixed $value): string
    {
        [$from, $to] = \is_string($value) ? $this->unserializeValue($value) : $value;

        return $from . ';' . $to;
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): array
    {
        [$from, $to] = $this->splitRange($serializedValue);

        return [
            $from === null ? null : (int)$from,
            $to === null ? null : (int)$to,
        ];
    }

    #[\Override]
    public function toFormFieldValue(mixed $value): string
    {
        return $this->serializeValue($value);
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        [$from, $to] = $value;

        return WCF::getLanguage()->getDynamicVariable('wcf.objectFilter.user.registrationDays.summary', [
            'from' => $from,
            'to' => $to,
        ]);
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
