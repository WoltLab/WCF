<?php

namespace wcf\system\object\filter;

use wcf\system\form\builder\field\DateRangeFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\NumericRangeFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;

/**
 * Provides the shared handling of filters whose value is a range with an
 * optional lower and an optional upper bound, serialized as `from;to`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
trait TRangeObjectFilter
{
    /**
     * Splits the serialized range into its lower and upper bound, empty bounds
     * are returned as `null`.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function splitRange(string $value): array
    {
        [$from, $to] = \explode(';', $value, 2) + [1 => ''];

        return [
            $from === '' ? null : $from,
            $to === '' ? null : $to,
        ];
    }

    /**
     * Returns the validator that requires at least one bound and rejects an
     * upper bound that is lower than the lower bound.
     */
    private function getRangeValidator(): FormFieldValidator
    {
        return new FormFieldValidator(
            'range',
            static function (IFormField $field) {
                \assert($field instanceof DateRangeFormField || $field instanceof NumericRangeFormField);
                $from = $field->getFromValue();
                $to = $field->getToValue();

                if ($from === '' && $to === '') {
                    $field->addValidationError(
                        new FormFieldValidationError('empty')
                    );
                } elseif ($from !== '' && $to !== '' && $from > $to) {
                    $field->addValidationError(
                        new FormFieldValidationError('endBeforeStart', 'wcf.objectFilter.range.error.endBeforeStart')
                    );
                }
            }
        );
    }
}
