<?php

namespace wcf\system\form\option\formatter;

use wcf\system\language\LanguageFactory;
use wcf\util\StringUtil;

/**
 * Formatter for currencies.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class CurrencyFormatter implements IFormOptionFormatter
{
    #[\Override]
    public function format(string $value, int $languageID, array $configuration): string
    {
        $showDecimals = (float)$value % 100 !== 0;
        $value = (float)$value / 100;
        $language = LanguageFactory::getInstance()->getLanguage($languageID);
        $suffix = '';
        if (!empty($configuration['currency'])) {
            $suffix = ' ' . StringUtil::encodeHTML($configuration['currency']);
        }

        return \number_format(
            \round($value, 2),
            $showDecimals ? 2 : 0,
            $language->get('wcf.global.decimalPoint'),
            $language->get('wcf.global.thousandsSeparator')
        ) . $suffix;
    }
}
