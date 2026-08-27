<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;

/**
 * JSON encodes the given value.
 *
 * Usage:
 *  { "title": {$foo->getTitle()|json} }
 *
 * Inside a `<script>` element `{@$var|json}` must be used, the result contains no
 * verbatim `<` and therefore cannot terminate the element. Within an HTML attribute
 * `{$var|json}` must be used, because the structural quotes of the JSON value itself are
 * not escaped and require the HTML-encoding.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.5
 */
class JsonModifierTemplatePlugin implements IModifierTemplatePlugin
{
    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        // `<` must never appear verbatim, otherwise a value containing `<!--<script `
        // pushes the HTML tokenizer out of the surrounding `<script>` element and the
        // remainder of the document is swallowed as script data.
        return \json_encode(
            $tagArgs[0],
            \JSON_THROW_ON_ERROR | \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_APOS | \JSON_HEX_QUOT
        );
    }
}
