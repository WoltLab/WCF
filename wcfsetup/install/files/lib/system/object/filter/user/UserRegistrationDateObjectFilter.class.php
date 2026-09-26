<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\DateRangeFormField;
use wcf\system\object\filter\IObjectListFilter;
use wcf\system\object\filter\TRangeObjectFilter;
use wcf\system\WCF;

/**
 * Filters users by their registration date. The value consists of an optional
 * first and an optional last day (`Y-m-d`), both inclusive and interpreted in
 * the default time zone. Guests never match.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @implements IObjectListFilter<User, array{0: ?string, 1: ?string}>
 */
final class UserRegistrationDateObjectFilter implements IObjectListFilter
{
    use TRangeObjectFilter;

    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userRegistrationDate';
    }

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.objectFilter.user.registrationDate');
    }

    #[\Override]
    public function getFormField(): DateRangeFormField
    {
        return DateRangeFormField::create('userRegistrationDate')
            ->label('wcf.user.condition.registrationDate')
            ->addValidator($this->getRangeValidator());
    }

    /**
     * @param string|array{0: ?string, 1: ?string} $value
     */
    #[\Override]
    public function serializeValue(mixed $value): string
    {
        [$from, $to] = \is_string($value) ? $this->splitRange($value) : $value;

        return $from . ';' . $to;
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): array
    {
        return $this->splitRange($serializedValue);
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        [$from, $to] = $value;

        $formatter = new \IntlDateFormatter(
            WCF::getLanguage()->getLocale(),
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::NONE,
            'UTC',
        );
        $format = static function (?string $date) use ($formatter): ?string {
            if ($date === null) {
                return null;
            }

            $dateTime = \DateTimeImmutable::createFromFormat('!Y-m-d', $date, new \DateTimeZone('UTC'));
            if ($dateTime === false) {
                return $date;
            }

            return $formatter->format($dateTime);
        };

        return WCF::getLanguage()->getDynamicVariable('wcf.objectFilter.user.registrationDate.summary', [
            'from' => $format($from),
            'to' => $format($to),
        ]);
    }

    #[\Override]
    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void
    {
        [$start, $end] = $this->getBoundaries($value);

        if ($start !== null) {
            $conditions->add('registrationDate >= ?', [$start]);
        }
        if ($end !== null) {
            $conditions->add('registrationDate < ?', [$end]);
        }
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        if ($object->isGuest()) {
            return false;
        }

        [$start, $end] = $this->getBoundaries($configuredValue);

        if ($start !== null && $object->registrationDate < $start) {
            return false;
        }
        if ($end !== null && $object->registrationDate >= $end) {
            return false;
        }

        return true;
    }

    /**
     * Returns the timestamp of the start of the first day and the timestamp
     * of the start of the day after the last day.
     *
     * @param array{0: ?string, 1: ?string} $value
     * @return array{0: ?int, 1: ?int}
     */
    private function getBoundaries(array $value): array
    {
        [$from, $to] = $value;
        $timezone = new \DateTimeZone(\TIMEZONE);

        $start = null;
        if ($from !== null) {
            $dateTime = \DateTimeImmutable::createFromFormat('!Y-m-d', $from, $timezone);
            if ($dateTime !== false) {
                $start = $dateTime->getTimestamp();
            }
        }

        $end = null;
        if ($to !== null) {
            $dateTime = \DateTimeImmutable::createFromFormat('!Y-m-d', $to, $timezone);
            if ($dateTime !== false) {
                $end = $dateTime->modify('+1 day')->getTimestamp();
            }
        }

        return [$start, $end];
    }
}
