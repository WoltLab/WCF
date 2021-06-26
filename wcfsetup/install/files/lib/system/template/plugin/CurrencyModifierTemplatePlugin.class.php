<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;
use wcf\system\WCF;

/**
 * Formats a currency value.
 * The default number of decimals is `2`.
 *
 * Defining the number of decimals is available since version 5.4.
 *
 * Usage:
 *  {$float|currency}
 *  {$float|currency:$numberOfDecimals}
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Template\Plugin
 */
class CurrencyModifierTemplatePlugin implements IModifierTemplatePlugin
{
    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        $decimals = \intval($tagArgs[1] ?? 2);

        return \number_format(
            \round($tagArgs[0], $decimals),
            $decimals,
            WCF::getLanguage()->get('wcf.global.decimalPoint'),
            WCF::getLanguage()->get('wcf.global.thousandsSeparator')
        );
    }
}
