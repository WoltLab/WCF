<?php

namespace wcf\system\image\cover\photo\generator;

use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;
use wcf\system\style\StyleHandler;

/**
 * Generates abstract SVG cover photos that are derived from a seed, the same
 * seed always yields the same image. The colors are taken from the style, the
 * hue is slightly shifted for each seed to create some variety.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CoverPhotoGenerator
{
    public const WIDTH = 1000;

    public const HEIGHT = 400;

    private static CoverPhotoGenerator $activeStyleGenerator;

    /**
     * @param ?CoverPhotoPalette $darkPalette palette that is used if the browser prefers a dark color scheme
     */
    public function __construct(
        private readonly CoverPhotoPalette $palette,
        private readonly ?CoverPhotoPalette $darkPalette = null,
    ) {}

    /**
     * Returns the generator that uses the colors of the active style and
     * respects the color scheme of the active user.
     */
    public static function forActiveStyle(): self
    {
        if (!isset(self::$activeStyleGenerator)) {
            $styleHandler = StyleHandler::getInstance();
            $style = $styleHandler->getStyle()->getDecoratedObject();

            self::$activeStyleGenerator = match ($styleHandler->getColorScheme()) {
                'dark' => new self(CoverPhotoPalette::fromStyle($style, true)),
                'system' => new self(
                    CoverPhotoPalette::fromStyle($style),
                    $style->hasDarkMode !== 0 ? CoverPhotoPalette::fromStyle($style, true) : null,
                ),
                default => new self(CoverPhotoPalette::fromStyle($style)),
            };
        }

        return self::$activeStyleGenerator;
    }

    /**
     * Returns the cover photo for the given seed as a data URI.
     */
    public function getDataUri(string $seed): string
    {
        return 'data:image/svg+xml;base64,' . \base64_encode($this->render($seed));
    }

    /**
     * Returns the SVG markup of the cover photo for the given seed.
     */
    public function render(string $seed): string
    {
        $randomizer = new Randomizer(new Xoshiro256StarStar(\hash('sha256', $seed, true)));

        $hueShift = $randomizer->getFloat(-45, 45);
        $css = 'svg{' . $this->getColorVariables($this->palette, $hueShift) . '}';
        if ($this->darkPalette !== null) {
            $css .= '@media (prefers-color-scheme:dark){svg{'
                . $this->getColorVariables($this->darkPalette, $hueShift)
                . '}}';
        }

        // Direction of the background gradient.
        [$x1, $y1, $x2, $y2] = [
            [0, 0, 1, 1],
            [1, 0, 0, 1],
            [0, 0, 1, 0],
            [0, 1, 1, 0],
        ][$randomizer->getInt(0, 3)];

        $shapes = match ($randomizer->getInt(0, 4)) {
            0 => $this->renderWaves($randomizer),
            1 => $this->renderGlows($randomizer),
            2 => $this->renderBands($randomizer),
            3 => $this->renderCircles($randomizer),
            4 => $this->renderFacets($randomizer),
        };

        $width = self::WIDTH;
        $height = self::HEIGHT;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$width} {$height}" width="{$width}" height="{$height}" preserveAspectRatio="xMidYMid slice"><style>{$css}</style><linearGradient id="g" x1="{$x1}" y1="{$y1}" x2="{$x2}" y2="{$y2}"><stop offset="0" style="stop-color:var(--c0)"/><stop offset="1" style="stop-color:var(--c1)"/></linearGradient><rect width="{$width}" height="{$height}" fill="url(#g)"/>{$shapes}</svg>
SVG;
    }

    /**
     * Derives the five colors `--c0` to `--c4` from the palette. The first two
     * are used for the background, the remaining ones for the shapes.
     */
    private function getColorVariables(CoverPhotoPalette $palette, float $hueShift): string
    {
        $primary = $palette->primary->rotateHue($hueShift);
        $accent = $palette->accent->rotateHue($hueShift);

        $colors = [
            $primary,
            $primary->rotateHue(30)->withLightness($primary->lightness - 0.08),
            $accent,
            $accent->rotateHue(-35)->withLightness($accent->lightness + 0.1),
            $primary->rotateHue(-30)->withLightness($primary->lightness + 0.18),
        ];

        $variables = '';
        foreach ($colors as $index => $color) {
            $variables .= "--c{$index}:{$color->toHex()};";
        }

        return $variables;
    }

    /**
     * Layered waves that stretch across the entire width.
     */
    private function renderWaves(Randomizer $randomizer): string
    {
        $layers = $randomizer->getInt(3, 4);
        $colorOffset = $randomizer->getInt(0, 2);

        $svg = '';
        for ($i = 0; $i < $layers; $i++) {
            $baseline = self::HEIGHT * (0.25 + 0.6 * $i / ($layers - 1));
            $amplitude = $randomizer->getInt(25, 70);
            $y = static fn() => (int)\round($baseline + $randomizer->getInt(-$amplitude, $amplitude));

            $path = \sprintf(
                'M0 %d C%d %d %d %d 500 %d S%d %d 1000 %dV%dH0Z',
                $y(),
                $randomizer->getInt(120, 220),
                $y(),
                $randomizer->getInt(280, 380),
                $y(),
                $y(),
                $randomizer->getInt(780, 880),
                $y(),
                $y(),
                self::HEIGHT,
            );

            $svg .= '<path d="' . $path . '" style="' . $this->fill(2 + ($i + $colorOffset) % 3, 0.35 + 0.15 * $i) . '"/>';
        }

        return $svg;
    }

    /**
     * Soft glows that blend into each other.
     */
    private function renderGlows(Randomizer $randomizer): string
    {
        $svg = '';
        foreach ([2, 3, 4] as $color) {
            $svg .= <<<SVG
<radialGradient id="r{$color}"><stop offset="0" style="stop-color:var(--c{$color})"/><stop offset="1" style="stop-color:var(--c{$color});stop-opacity:0"/></radialGradient>
SVG;
        }

        $count = $randomizer->getInt(4, 6);
        for ($i = 0; $i < $count; $i++) {
            $svg .= \sprintf(
                '<circle cx="%d" cy="%d" r="%d" fill="url(#r%d)" opacity="%s"/>',
                $randomizer->getInt(0, self::WIDTH),
                $randomizer->getInt(-50, self::HEIGHT + 50),
                $randomizer->getInt(150, 350),
                $randomizer->getInt(2, 4),
                $this->opacity($randomizer->getFloat(0.5, 0.9)),
            );
        }

        return $svg;
    }

    /**
     * Diagonal bands of varying width.
     */
    private function renderBands(Randomizer $randomizer): string
    {
        $angle = $randomizer->getInt(20, 70) * ($randomizer->getInt(0, 1) === 0 ? 1 : -1);

        $svg = \sprintf('<g transform="rotate(%d %d %d)">', $angle, self::WIDTH / 2, self::HEIGHT / 2);
        $x = -400 + $randomizer->getInt(0, 100);
        while ($x < self::WIDTH + 400) {
            $width = $randomizer->getInt(30, 180);
            $svg .= \sprintf(
                '<rect x="%d" y="-600" width="%d" height="1600" style="%s"/>',
                $x,
                $width,
                $this->fill($randomizer->getInt(2, 4), $randomizer->getFloat(0.15, 0.45)),
            );
            $x += $width + $randomizer->getInt(20, 120);
        }

        return $svg . '</g>';
    }

    /**
     * Scattered circles of different sizes.
     */
    private function renderCircles(Randomizer $randomizer): string
    {
        $svg = '';
        $count = $randomizer->getInt(14, 22);
        for ($i = 0; $i < $count; $i++) {
            $svg .= \sprintf(
                '<circle cx="%d" cy="%d" r="%d" style="%s"/>',
                $randomizer->getInt(0, self::WIDTH),
                $randomizer->getInt(0, self::HEIGHT),
                $randomizer->getInt(15, 110),
                $this->fill($randomizer->getInt(2, 4), $randomizer->getFloat(0.15, 0.5)),
            );
        }

        return $svg;
    }

    /**
     * Low poly facets that lighten or darken the background.
     */
    private function renderFacets(Randomizer $randomizer): string
    {
        $columns = 8;
        $rows = 4;
        $cellWidth = self::WIDTH / $columns;
        $cellHeight = self::HEIGHT / $rows;

        $points = [];
        for ($row = 0; $row <= $rows; $row++) {
            for ($column = 0; $column <= $columns; $column++) {
                $x = $column * $cellWidth;
                $y = $row * $cellHeight;

                // Only move the inner points to keep the edges straight.
                if ($column !== 0 && $column !== $columns) {
                    $x += $randomizer->getFloat(-0.35, 0.35) * $cellWidth;
                }
                if ($row !== 0 && $row !== $rows) {
                    $y += $randomizer->getFloat(-0.35, 0.35) * $cellHeight;
                }

                $points[$row][$column] = (int)\round($x) . ' ' . (int)\round($y);
            }
        }

        // Triangles are grouped by their shade to keep the markup small.
        $shades = [
            1 => ['#fff', 0.06, ''],
            2 => ['#fff', 0.12, ''],
            3 => ['#000', 0.06, ''],
            4 => ['#000', 0.12, ''],
        ];
        for ($row = 0; $row < $rows; $row++) {
            for ($column = 0; $column < $columns; $column++) {
                $topLeft = $points[$row][$column];
                $topRight = $points[$row][$column + 1];
                $bottomLeft = $points[$row + 1][$column];
                $bottomRight = $points[$row + 1][$column + 1];

                if ($randomizer->getInt(0, 1) === 0) {
                    $triangles = [[$topLeft, $topRight, $bottomRight], [$topLeft, $bottomRight, $bottomLeft]];
                } else {
                    $triangles = [[$topLeft, $topRight, $bottomLeft], [$topRight, $bottomRight, $bottomLeft]];
                }

                foreach ($triangles as [$a, $b, $c]) {
                    $shade = $randomizer->getInt(0, 4);
                    if ($shade !== 0) {
                        $shades[$shade][2] .= "M{$a}L{$b}L{$c}Z";
                    }
                }
            }
        }

        $svg = '';
        foreach ($shades as [$color, $opacity, $path]) {
            if ($path !== '') {
                $svg .= \sprintf('<path d="%s" fill="%s" fill-opacity="%s"/>', $path, $color, $opacity);
            }
        }

        return $svg;
    }

    private function fill(int $color, float $opacity): string
    {
        return "fill:var(--c{$color});fill-opacity:{$this->opacity($opacity)}";
    }

    private function opacity(float $opacity): string
    {
        return (string)\round($opacity, 2);
    }
}
