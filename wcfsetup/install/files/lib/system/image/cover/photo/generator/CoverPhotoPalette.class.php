<?php

namespace wcf\system\image\cover\photo\generator;

use wcf\data\style\Style;

/**
 * Base colors of generated cover photos, derived from the style.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CoverPhotoPalette
{
    public function __construct(
        public readonly OklchColor $primary,
        public readonly OklchColor $accent,
    ) {}

    public static function fromStyle(Style $style, bool $darkMode = false): self
    {
        $prefix = $darkMode ? Style::DARK_MODE_PREFIX : '';

        return new self(
            self::getColor($style, $prefix, 'wcfHeaderBackground', OklchColor::fromRgb(58, 109, 156)),
            self::getColor($style, $prefix, 'wcfButtonPrimaryBackground', OklchColor::fromRgb(29, 122, 197)),
        );
    }

    private static function getColor(Style $style, string $prefix, string $variableName, OklchColor $fallback): OklchColor
    {
        $value = $style->getVariable($prefix . $variableName, true);

        // Dark mode variables are empty if they are identical to the light mode.
        if ($prefix !== '' && ($value === null || $value === '')) {
            $value = $style->getVariable($variableName, true);
        }

        return OklchColor::fromString($value ?? '') ?? $fallback;
    }
}
