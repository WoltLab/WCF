<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\TimeRangeFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\object\filter\AbstractRangeObjectFilter;
use wcf\system\WCF;

/**
 * Filters by whether the current time of day is within the given range, based
 * on the time zone of the user. The start time is inclusive, the end time is
 * exclusive. If the start time is later than the end time, the range spans
 * midnight, e.g. `21:00;09:00` matches from 21:00 until 08:59.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractRangeObjectFilter<User, string>
 */
final class TimeRangeObjectFilter extends AbstractRangeObjectFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.timeRange';
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.timeRange';
    }

    #[\Override]
    protected function createFormField(): TimeRangeFormField
    {
        return TimeRangeFormField::create('timeRange')
            ->label('wcf.objectFilter.timeRange');
    }

    #[\Override]
    public function getFormField(): TimeRangeFormField
    {
        $formField = parent::getFormField();
        \assert($formField instanceof TimeRangeFormField);

        return $formField->addValidator(new FormFieldValidator(
            'sameTime',
            static function (IFormField $field) {
                \assert($field instanceof TimeRangeFormField);

                if ($field->getFromValue() !== '' && $field->getFromValue() === $field->getToValue()) {
                    $field->addValidationError(
                        new FormFieldValidationError(
                            'sameTime',
                            'wcf.objectFilter.timeRange.error.sameTime'
                        )
                    );
                }
            }
        ));
    }

    #[\Override]
    protected function supportsInvertedRange(): bool
    {
        return true;
    }

    #[\Override]
    protected function unserializeBound(string $bound): string
    {
        return $bound;
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        [$from, $to] = $value;

        $formatter = new \IntlDateFormatter(
            WCF::getLanguage()->getLocale(),
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            'UTC',
        );
        $format = static function (?string $time) use ($formatter): ?string {
            if ($time === null) {
                return null;
            }

            $dateTime = \DateTimeImmutable::createFromFormat('!H:i', $time, new \DateTimeZone('UTC'));
            if ($dateTime === false) {
                return $time;
            }

            return $formatter->format($dateTime);
        };

        return WCF::getLanguage()->getDynamicVariable('wcf.objectFilter.timeRange.summary', [
            'from' => $format($from),
            'to' => $format($to),
            'overnight' => $from !== null && $to !== null && $from > $to,
        ]);
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        [$from, $to] = $configuredValue;

        $now = (new \DateTimeImmutable('@' . \TIME_NOW))
            ->setTimezone($object->getTimeZone())
            ->format('H:i');

        if ($from === null) {
            return $now < $to;
        }

        if ($to === null) {
            return $now >= $from;
        }

        if ($from <= $to) {
            return $now >= $from && $now < $to;
        }

        // The range spans midnight.
        return $now >= $from || $now < $to;
    }
}
