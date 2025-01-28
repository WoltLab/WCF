<?php

namespace wcf\system\gridView\renderer;

use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Formats the content of a column as a currency.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class CurrencyColumnRenderer extends AbstractColumnRenderer
{
    public function __construct(
        private readonly string $currency,
    ) {}

    #[\Override]
    public function render(mixed $value, DatabaseObject $row): string
    {
        return \number_format(
            \round($value, 2),
            2,
            WCF::getLanguage()->get('wcf.global.decimalPoint'),
            WCF::getLanguage()->get('wcf.global.thousandsSeparator')
        ) . ' ' . StringUtil::encodeHTML($this->currency);
    }

    #[\Override]
    public function getClasses(): string
    {
        return 'gridView__column--digits';
    }
}
