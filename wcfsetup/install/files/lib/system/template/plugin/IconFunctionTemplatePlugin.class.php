<?php

namespace wcf\system\template\plugin;

use wcf\system\exception\SystemException;
use wcf\system\template\TemplateEngine;

/**
 * Template compiler plugin that embeds icons into the page. The
 * supported sizes are 16, 24, 32, 48, 64, 96, 128 and 144.
 *
 * Usage:
 *  {icon size=16 name='bell'}
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Template\Plugin
 * @since 6.0
 */
final class IconFunctionTemplatePlugin implements IFunctionTemplatePlugin
{
    private const SIZES = [16, 24, 32, 48, 64, 96, 128, 144];

    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        $size = \intval($tagArgs['size'] ?? 0);
        $name = $tagArgs['name'] ?? '';

        if (!\in_array($size, self::SIZES)) {
            throw new \InvalidArgumentException("An unsupported size `{$size}` was requested.");
        }

        if ($name === '') {
            throw new \InvalidArgumentException("The `name` attribute must be present and non-empty");
        }

        $svgFile = \WCF_DIR . "icon/font-awesome/v6/brands/{$name}.svg";
        if (\file_exists($svgFile)) {
            $content = \file_get_contents($svgFile);
            return <<<HTML
            <fa-icon size="{$size}" brand>{$content}</fa-icon>
            HTML;
        }

        return <<<HTML
        <fa-icon size="{$size}" name="{$name}"></fa-icon>
        HTML;
    }
}
