<?php

namespace wcf\system\object\filter;

use wcf\data\DatabaseObject;
use wcf\system\form\builder\field\DateRangeFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\NumericRangeFormField;
use wcf\system\form\builder\field\TimeRangeFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\WCF;

/**
 * Default implementation of a filter whose value is a range with an optional
 * lower and an optional upper bound, serialized as `from;to`. At least one
 * bound is required.
 *
 * The language item returned by `getLanguageItem()` is used as the title, the
 * summary is read from the language item `<languageItem>.summary` that receives
 * the bounds as `$from` and `$to`, missing bounds are `null`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @template TDatabaseObject of DatabaseObject
 * @template TBound of int|string
 * @implements IObjectFilter<TDatabaseObject, array{0: ?TBound, 1: ?TBound}>
 */
abstract class AbstractRangeObjectFilter implements IObjectFilter
{
    /**
     * Returns the language item of the title of this filter.
     */
    abstract protected function getLanguageItem(): string;

    /**
     * Creates the form field to enter the range, the validation of the bounds
     * is added by `getFormField()`.
     */
    abstract protected function createFormField(): DateRangeFormField|NumericRangeFormField|TimeRangeFormField;

    /**
     * Converts a non-empty bound into its native type.
     *
     * @return TBound
     */
    abstract protected function unserializeBound(string $bound): int|string;

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->getLanguageItem());
    }

    #[\Override]
    public function getFormField(): DateRangeFormField|NumericRangeFormField|TimeRangeFormField
    {
        $supportsInvertedRange = $this->supportsInvertedRange();

        return $this->createFormField()
            ->addValidator(new FormFieldValidator(
                'range',
                static function (IFormField $field) use ($supportsInvertedRange) {
                    \assert(
                        $field instanceof DateRangeFormField
                        || $field instanceof NumericRangeFormField
                        || $field instanceof TimeRangeFormField
                    );
                    $from = $field->getFromValue();
                    $to = $field->getToValue();

                    if ($from === '' && $to === '') {
                        $field->addValidationError(
                            new FormFieldValidationError('empty')
                        );
                    } elseif (!$supportsInvertedRange && $from !== '' && $to !== '' && $from > $to) {
                        $field->addValidationError(
                            new FormFieldValidationError(
                                'endBeforeStart',
                                'wcf.objectFilter.range.error.endBeforeStart'
                            )
                        );
                    }
                }
            ));
    }

    /**
     * Returns true if the lower bound may be greater than the upper bound, e.g.
     * for a time range spanning midnight.
     */
    protected function supportsInvertedRange(): bool
    {
        return false;
    }

    #[\Override]
    public function isAvailable(): bool
    {
        return true;
    }

    #[\Override]
    public function isRepeatable(): bool
    {
        return false;
    }

    /**
     * @param string|array{0: ?TBound, 1: ?TBound} $value
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
        [$from, $to] = \explode(';', $serializedValue, 2) + [1 => ''];

        return [
            $from === '' ? null : $this->unserializeBound($from),
            $to === '' ? null : $this->unserializeBound($to),
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

        return WCF::getLanguage()->getDynamicVariable($this->getLanguageItem() . '.summary', [
            'from' => $from,
            'to' => $to,
        ]);
    }
}
