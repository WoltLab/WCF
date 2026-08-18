<?php

namespace wcf\system\template\plugin;

use wcf\system\style\exception\IconValidationFailed;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\style\FontAwesomeIconBrand;
use wcf\system\style\IFontAwesomeIcon;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * Template function plugin that embeds icons into the page. The
 * supported sizes are 16, 24, 32, 48, 64, 96, 128 and 144.
 *
 * Usage:
 *  {icon name='bell'}
 *  {icon size=32 name='caret-down' type='solid'}
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class IconFunctionTemplatePlugin implements IFunctionTemplatePlugin
{
    private const SIZES = [16, 24, 32, 48, 64, 96, 128, 144];

    private const TYPES = ['brand', 'solid'];

    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        $size = \intval($tagArgs['size'] ?? 16);
        $name = $tagArgs['name'] ?? '';
        $type = $tagArgs['type'] ?? '';
        $encodeJson = $tagArgs['encodeJson'] ?? '';

        if (!\in_array($size, self::SIZES)) {
            throw new \InvalidArgumentException("An unsupported size '{$size}' was requested.");
        }

        if ($name === '') {
            throw new \InvalidArgumentException("The 'name' attribute must be present and non-empty.");
        }

        if ($type !== '' && !\in_array($type, self::TYPES)) {
            throw new \InvalidArgumentException("An unsupported type '{$type}' was specified.");
        }

        try {
            $html = $this->getIcon($type, $name)->toHtml($size);
        } catch (IconValidationFailed) {
            $attributes = [];
            foreach ($tagArgs as $key => $value) {
                $attributes[] = \sprintf(
                    "%s='%s'",
                    StringUtil::encodeHTML((string)$key),
                    StringUtil::encodeHTML((string)$value),
                );
            }

            $fallback = \sprintf(
                '{icon %s}',
                \implode(' ', $attributes),
            );

            if ($encodeJson) {
                return \json_encode($fallback, \JSON_THROW_ON_ERROR);
            }

            return $fallback;
        }

        if ($encodeJson) {
            return \json_encode($html, \JSON_THROW_ON_ERROR);
        }

        return $html;
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
