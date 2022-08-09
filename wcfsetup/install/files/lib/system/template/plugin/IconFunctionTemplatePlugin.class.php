<?php

namespace wcf\system\template\plugin;

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

    private const TYPES = ['brand', 'regular', 'solid'];

    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        $size = \intval($tagArgs['size'] ?? 0);
        $name = $tagArgs['name'] ?? '';
        $type = $tagArgs['type'] ?? '';

        if (!\in_array($size, self::SIZES)) {
            throw new \InvalidArgumentException("An unsupported size `{$size}` was requested.");
        }

        if ($name === '') {
            throw new \InvalidArgumentException("The `name` attribute must be present and non-empty.");
        }

        if ($type === '') {
            throw new \InvalidArgumentException("The `type` attribute must be present and non-empty.");
        } else if (!\in_array($type, self::TYPES)) {
            throw new \InvalidArgumentException("An unsupported type `${type}` was specified.");
        }

        if ($type === 'brand') {
            $svgFile = \WCF_DIR . "icon/font-awesome/v6/brands/{$name}.svg";
            if (!\file_exists($svgFile)) {
                throw new \InvalidArgumentException("Unable to locate the icon for brand `${name}`.");
            }

            $content = \file_get_contents($svgFile);
            $content = \preg_replace('~^<svg~', '<svg slot="svg"', $content);
            return <<<HTML
            <fa-brand size="{$size}">{$content}</fa-brand>
            HTML;
        }

        return <<<HTML
        <fa-icon size="{$size}" name="{$name}" {$type}></fa-icon>
        HTML;
    }
}
