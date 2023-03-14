<?php

namespace wcf\system\style;

use wcf\system\style\exception\InvalidIconSize;
use wcf\system\style\exception\UnknownIcon;

/**
 * Represents a Font Awesome brand icon.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class FontAwesomeIconBrand implements IFontAwesomeIcon
{
    public const SIZES = IFontAwesomeIcon::SIZES;

    private function __construct(
        private readonly string $name
    ) {
        self::validateName($name);
    }

    /**
     * Renders the HTML representation of an icon.
     *
     * @throws InvalidIconSize
     */
    public function toHtml(int $size = 16): string
    {
        if (!\in_array($size, self::SIZES)) {
            throw new InvalidIconSize($size);
        }

        $content = \preg_replace(
            '~^<svg~',
            '<svg slot="svg"',
            \file_get_contents(self::getFilename($this->name))
        );

        return <<<HTML
        <fa-brand size="{$size}">{$content}</fa-brand>
        HTML;
    }

    public static function fromName(string $name): self
    {
        return new self($name);
    }

    public static function isValidName(string $name): bool
    {
        return \file_exists(self::getFilename($name));
    }

    private static function validateName(string $name): void
    {
        if (!self::isValidName($name)) {
            throw new UnknownIcon($name);
        }
    }

    private static function getFilename(string $name): string
    {
        return \WCF_DIR . "icon/font-awesome/v6/brands/{$name}.svg";
    }
}
