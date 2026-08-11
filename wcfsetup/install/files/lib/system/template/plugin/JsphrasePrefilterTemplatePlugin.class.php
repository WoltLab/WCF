<?php

namespace wcf\system\template\plugin;

use wcf\data\language\Language;
use wcf\system\template\TemplateScriptingCompiler;

/**
 * Registers static phrases for use in JavaScript/TypeScript
 * modules on runtime. Dynamic phrases or does requiring
 * a literal handling need to be manually registered.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class JsphrasePrefilterTemplatePlugin implements IPrefilterTemplatePlugin
{
    /**
     * @inheritDoc
     */
    public function execute($templateName, $sourceContent, TemplateScriptingCompiler $compiler)
    {
        $ldq = \preg_quote($compiler->getLeftDelimiter(), '~');
        $rdq = \preg_quote($compiler->getRightDelimiter(), '~');

        return \preg_replace_callback(
            "~{$ldq}jsphrase name='(?<name>[^']+)'{$rdq}~",
            static function ($match) {
                $name = $match['name'];

                // Invalid names are left untouched, the tag is then reported
                // by the accompanying function plugin at runtime.
                if (!\preg_match(Language::PHRASE_PATTERN, $name)) {
                    return $match[0];
                }

                return \sprintf(
                    "WoltLabLanguage.registerPhrase('%s', '{jslang}%s{/jslang}');",
                    $name,
                    $name,
                );
            },
            $sourceContent
        );
    }
}
