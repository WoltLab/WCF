<?php

namespace wcf\system\view\filter;

use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\NumericRangeFormField;

/**
 * Filter for columns containing integers.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class IntegerFilter extends NumericFilter
{
    public function __construct(
        string $id,
        string $languageItem,
        string $databaseColumn = '',
        protected readonly ?int $minimum = null,
        protected readonly ?int $maximum = null,
    ) {
        parent::__construct($id, $languageItem, $databaseColumn);
    }

    #[\Override]
    public function getFormField(): AbstractFormField
    {
        return NumericRangeFormField::create($this->id)
            ->label($this->languageItem)
            ->nullable()
            ->integerValues()
            ->minimum($this->minimum)
            ->maximum($this->maximum);
    }
}
