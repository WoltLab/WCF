<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;
use wcf\system\WCF;

/**
 * Template modifier plugin which returns the value of language variables.
 *
 * Usage:
 *  {$string|phrase}
 *
 * @author  Marcel Werk
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Template\Plugin
 * @since 5.5
 */
class PhraseModifierTemplatePlugin implements IModifierTemplatePlugin
{
    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        if ($tagArgs[0] === null) {
            return '';
        }

        if (($lang = $tplObj->get('__language')) === null) {
            $lang = WCF::getLanguage();
        }

        return $lang->get($tagArgs[0]);
    }
}
