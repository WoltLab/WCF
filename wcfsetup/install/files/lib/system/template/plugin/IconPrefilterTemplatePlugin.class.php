<?php

namespace wcf\system\template\plugin;

use wcf\system\style\exception\IconValidationFailed;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\template\TemplateScriptingCompiler;

/**
 * See IconFunctionTemplatePlugin.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class IconPrefilterTemplatePlugin implements IPrefilterTemplatePlugin
{
    /**
     * @inheritDoc
     */
    public function execute($templateName, $sourceContent, TemplateScriptingCompiler $compiler)
    {
        $ldq = \preg_quote($compiler->getLeftDelimiter(), '~');
        $rdq = \preg_quote($compiler->getRightDelimiter(), '~');

        $sizes = \implode('|', FontAwesomeIcon::SIZES);

        return \preg_replace_callback(
            "~{$ldq}icon(?: size=(?<size>{$sizes}))? name='(?<name>[a-z0-9-]+)'(?: type='(?<type>solid)')?{$rdq}~",
            static function ($match) {
                $size = ($match['size'] ?? null) ?: 16;
                $name = $match['name'];
                $solid = isset($match['type']) && $match['type'] === 'solid';

                try {
                    return FontAwesomeIcon::fromValues($name, $solid)->toHtml($size);
                } catch (IconValidationFailed) {
                    return $match[0];
                }
            },
            $sourceContent
        );
    }
}
