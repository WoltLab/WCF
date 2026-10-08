<?php

namespace wcf\system\image\cover\photo\generator;

/**
 * Immutable color in the OKLCH color space, allows the hue and lightness to be
 * shifted while keeping the perceived appearance of the color intact.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 * @see         https://bottosson.github.io/posts/oklab/
 */
final class OklchColor
{
    public function __construct(
        public readonly float $lightness,
        public readonly float $chroma,
        public readonly float $hue,
    ) {}

    /**
     * Parses a color in the notation `#rgb`, `#rrggbb` or `rgb(a)(r, g, b[, a])`,
     * the alpha channel is ignored.
     */
    public static function fromString(string $color): ?self
    {
        $color = \trim($color);

        if (\preg_match('~^#([0-9a-f]{3}|[0-9a-f]{6})$~i', $color, $matches)) {
            $hex = $matches[1];
            if (\strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }

            return self::fromRgb(
                \hexdec(\substr($hex, 0, 2)),
                \hexdec(\substr($hex, 2, 2)),
                \hexdec(\substr($hex, 4, 2)),
            );
        }

        if (\preg_match('~^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*[\d.]+\s*)?\)$~', $color, $matches)) {
            return self::fromRgb((int)$matches[1], (int)$matches[2], (int)$matches[3]);
        }

        return null;
    }

    public static function fromRgb(int $red, int $green, int $blue): self
    {
        $r = self::toLinear($red / 255);
        $g = self::toLinear($green / 255);
        $b = self::toLinear($blue / 255);

        $l = self::cbrt(0.4122214708 * $r + 0.5363325363 * $g + 0.0514459929 * $b);
        $m = self::cbrt(0.2119034982 * $r + 0.6806995451 * $g + 0.1073969566 * $b);
        $s = self::cbrt(0.0883024619 * $r + 0.2817188376 * $g + 0.6299787005 * $b);

        $lightness = 0.2104542553 * $l + 0.7936177850 * $m - 0.0040720468 * $s;
        $a = 1.9779984951 * $l - 2.4285922050 * $m + 0.4505937099 * $s;
        $b = 0.0259040371 * $l + 0.7827717662 * $m - 0.8086757660 * $s;

        return new self(
            $lightness,
            \sqrt($a * $a + $b * $b),
            \fmod(\rad2deg(\atan2($b, $a)) + 360, 360),
        );
    }

    public function withLightness(float $lightness): self
    {
        return new self(\max(0, \min(1, $lightness)), $this->chroma, $this->hue);
    }

    public function withChroma(float $chroma): self
    {
        return new self($this->lightness, \max(0, $chroma), $this->hue);
    }

    public function rotateHue(float $degrees): self
    {
        return new self($this->lightness, $this->chroma, \fmod($this->hue + $degrees + 360, 360));
    }

    /**
     * Returns the color in the notation `#rrggbb`, colors outside of the sRGB
     * gamut are mapped into it by reducing the chroma.
     */
    public function toHex(): string
    {
        $rgb = $this->toLinearRgb($this->chroma);
        if (!self::isInGamut($rgb)) {
            $low = 0;
            $high = $this->chroma;
            for ($i = 0; $i < 16; $i++) {
                $chroma = ($low + $high) / 2;
                if (self::isInGamut($this->toLinearRgb($chroma))) {
                    $low = $chroma;
                } else {
                    $high = $chroma;
                }
            }

            $rgb = $this->toLinearRgb($low);
        }

        return \sprintf(
            '#%02x%02x%02x',
            ...\array_map(
                static fn(float $channel) => (int)\round(\max(0, \min(1, self::fromLinear($channel))) * 255),
                $rgb
            )
        );
    }

    /**
     * @return array{float, float, float}
     */
    private function toLinearRgb(float $chroma): array
    {
        $hue = \deg2rad($this->hue);
        $a = $chroma * \cos($hue);
        $b = $chroma * \sin($hue);

        $l = ($this->lightness + 0.3963377774 * $a + 0.2158037573 * $b) ** 3;
        $m = ($this->lightness - 0.1055613458 * $a - 0.0638541728 * $b) ** 3;
        $s = ($this->lightness - 0.0894841775 * $a - 1.2914855480 * $b) ** 3;

        return [
            4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s,
            -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s,
            -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s,
        ];
    }

    /**
     * @param array{float, float, float} $rgb
     */
    private static function isInGamut(array $rgb): bool
    {
        foreach ($rgb as $channel) {
            if ($channel < -0.0001 || $channel > 1.0001) {
                return false;
            }
        }

        return true;
    }

    private static function toLinear(float $channel): float
    {
        return $channel <= 0.04045 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4;
    }

    private static function fromLinear(float $channel): float
    {
        return $channel <= 0.0031308 ? $channel * 12.92 : 1.055 * ($channel ** (1 / 2.4)) - 0.055;
    }

    private static function cbrt(float $value): float
    {
        return $value < 0 ? -((-$value) ** (1 / 3)) : $value ** (1 / 3);
    }
}
