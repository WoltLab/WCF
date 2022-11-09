<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateScriptingCompiler;

/**
 * Registers static phrases for use in JavaScript/TypeScript
 * modules on runtime. Dynamic phrases or does requiring
 * a literal handling need to be manually registered.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Template\Plugin
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
            "~{$ldq}jsphrase name='(?<name>[A-z0-9-_]+(\.[A-z0-9-_]+){2,})'{$rdq}~",
            static function ($match) {
                $name = $match['name'];

                return \sprintf(
                    "WoltLabLanguage.add('%s', '{jslang}%s{/jslang}');",
                    $name,
                    $name,
                );
            },
            $sourceContent
        );
    }
}
