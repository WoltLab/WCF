<?php

namespace wcf\system\style;

use wcf\system\style\exception\InvalidIconFormat;
use wcf\system\style\exception\InvalidIconSize;
use wcf\system\style\exception\UnknownIcon;

/**
 * Represents an icon for Font Awesome.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class FontAwesomeIcon implements IFontAwesomeIcon, \Stringable
{
    public const SIZES = IFontAwesomeIcon::SIZES;

    /**
     * Uses the icon name as the key and a boolean value that
     * indicates that an icon has a non-solid variant.
     */
    private static array $icons;

    private function __construct(
        private readonly string $name,
        private readonly bool $forceSolid
    ) {
        self::validateName($name);
    }

    /**
     * Serializes the icon name and the use of the solid style into a
     * semicolon separated string that is understood by `fromString`.
     */
    public function __toString(): string
    {
        return \sprintf(
            "%s;%s",
            $this->name,
            $this->forceSolid ? 'true' : 'false'
        );
    }

    /**
     * @inheritDoc
     */
    public function toHtml(int $size = 16): string
    {
        if (!\in_array($size, self::SIZES)) {
            throw new InvalidIconSize($size);
        }

        if ($this->forceSolid) {
            return \sprintf(
                '<fa-icon size="%d" name="%s" solid></fa-icon>',
                $size,
                $this->name,
            );
        }

        return \sprintf(
            '<fa-icon size="%d" name="%s"></fa-icon>',
            $size,
            $this->name,
        );
    }

    /**
     * Uses a string containing the two values separated by a semicolon.
     * The first value is the name of the icon and the second value is
     * the string `true` or `false` to force the solid variant.
     *
     * @throws InvalidIconFormat
     */
    public static function fromString(string $iconData): self
    {
        if (!\str_contains($iconData, ';')) {
            throw new InvalidIconFormat();
        }

        [$name, $solid] = \explode(';', $iconData, 2);
        if ($solid !== 'true' && $solid !== 'false') {
            throw new InvalidIconFormat();
        }

        $forceSolid = $solid === 'true';

        return self::fromValues($name, $forceSolid);
    }

    public static function fromValues(string $name, bool $forceSolid = false): self
    {
        return new self($name, $forceSolid);
    }

    public static function isValidString(string $iconData): bool
    {
        if (!\str_contains($iconData, ';')) {
            return false;
        }

        [$name, $solid] = \explode(';', $iconData, 2);
        if ($solid !== 'true' && $solid !== 'false') {
            return false;
        }

        return self::isValidName($name);
    }

    public static function isValidName(string $name): bool
    {
        if (!isset(self::$icons)) {
            self::$icons = require(__DIR__ . '/fa-metadata.php');
        }

        return isset(self::$icons[$name]);
    }

    private static function validateName(string $name): void
    {
        if (!self::isValidName($name)) {
            throw new UnknownIcon($name);
        }
    }

    /**
     * Maps a legacy FontAwesome 4 icon. Returns a string that may be passed
     * into self::fromString().
     *
     * This method must only be used for one-off migration processes during the upgrade
     * from WoltLab Suite 5.5 to 6.0, e.g. to update stored icon names within the database.
     *
     * @throws UnknownIcon if the icon cannot be mapped.
     * @deprecated 6.0 This method exists for migration purposes only.
     * @since 6.0
     */
    public static function mapVersion4(string $oldName): string
    {
        static $loaded = false;
        static $iconShim;
        static $aliases;
        static $hasNonSolidStyle;

        if ($loaded === false) {
            require(__DIR__ . '/fa-metadata-v4.php');

            $iconShim = \json_decode($jsonMigrate, true);
            $tmp = \json_decode($jsonAliases, true);
            $aliases = [];
            foreach ($tmp as $iconAlias) {
                [$alias, $name] = $iconAlias;
                $aliases[$alias] = $name;
            }
            $tmp = \json_decode($jsonHasNonSolidStyle, true);
            $hasNonSolidStyle = [];
            foreach ($tmp as $styleData) {
                [$name, $hasNonSolid] = $styleData;
                $hasNonSolidStyle[$name] = $hasNonSolid;
            }

            $loaded = true;
        }

        $name = $oldName;

        if (isset($iconShim[$name])) {
            [
                'name' => $newIconName,
                'type' => $type,
            ] = $iconShim[$name];

            // The regular user icon is used for consistency.
            if ($name === 'user') {
                $type = 'regular';
            }
        } else {
            // Not all icons are renamed.
            $newIconName = $name;
            $type = 'solid';
        }

        if (isset($aliases[$newIconName])) {
            $newIconName = $aliases[$newIconName];
        }

        // If the icon only ships in `solid` style then we can safely
        // use any other variant too. The `solid` style is therefore
        // only enforced when there is a non-solid variant too.
        if ($type === 'solid' && $newIconName !== 'caret-down' && !$hasNonSolidStyle[$newIconName]) {
            $type = 'regular';
        }

        return self::fromValues($newIconName, $type === 'solid')->__toString();
    }
}
