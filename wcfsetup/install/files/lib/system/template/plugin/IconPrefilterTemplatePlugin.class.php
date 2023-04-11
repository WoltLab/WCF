<?php

namespace wcf\system\template\plugin;

use wcf\system\style\exception\IconValidationFailed;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\style\FontAwesomeIconBrand;
use wcf\system\style\IFontAwesomeIcon;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\util\JSON;

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
            "~{$ldq}icon(?: size=(?<size>{$sizes}))? name='(?<name>[a-z0-9-]+)'(?: type='(?<type>solid|brand)')?(?: encodeJson=(?<encodeJson>true))?{$rdq}~",
            function ($match) {
                $size = ($match['size'] ?? null) ?: 16;
                $name = $match['name'];
                $type = ($match['type'] ?? '');
                $encodeJson = ($match['encodeJson'] ?? 'false') === 'true';

                try {
                    $html = $this->getIcon($type, $name)->toHtml($size);

                    if ($encodeJson) {
                        return JSON::encode($html);
                    }

                    return $html;
                } catch (IconValidationFailed) {
                    return $match[0];
                }
            },
            $sourceContent
        );
    }

    private function getIcon(string $type, string $name): IFontAwesomeIcon
    {
        if ($type === 'brand') {
            return FontAwesomeIconBrand::fromName($name);
        }

        $forceSolid = $type === 'solid';

        return FontAwesomeIcon::fromValues($name, $forceSolid);
    }
}
