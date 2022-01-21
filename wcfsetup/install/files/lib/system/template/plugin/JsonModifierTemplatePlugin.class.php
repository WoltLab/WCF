<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;
use wcf\util\JSON;

/**
 * JSON encodes the given value.
 *
 * Usage:
 *  { "title": {$foo->getTitle()|json} }
 *
 * Depending on the location you might need to either HTML-encode the resulting JSON string
 * or not. Within a `<script>` tag, additional HTML-encoding usually is an error, as HTML is
 * not interpreted within there, thus `{@$var|json}` with the additional `@` will need to be
 * used.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Template\Plugin
 * @since 5.5
 */
class JsonModifierTemplatePlugin implements IModifierTemplatePlugin
{
    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        return JSON::encode($tagArgs[0]);
    }
}
