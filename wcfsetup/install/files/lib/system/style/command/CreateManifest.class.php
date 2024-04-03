<?php

namespace wcf\system\style\command;

use wcf\data\page\PageCache;
use wcf\data\style\Style;
use wcf\system\io\AtomicWriter;
use wcf\system\language\LanguageFactory;
use wcf\util\JSON;

/**
 * Generate then `manifest-*.json` files for a style.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class CreateManifest
{
    private readonly Style $style;

    public function __construct(Style $style)
    {
        $this->style = $style;
    }

    public function __invoke(): void
    {
        $this->style->loadVariables();
        $headerColor = $this->style->getVariable('wcfHeaderBackground', true);
        $backgroundColor = $this->style->getVariable('wcfContentBackground', true);
        $homeLocation = JSON::encode(PageCache::getInstance()->getLandingPage()->getLink());

        $icons = [];
        // If no favicon is set, use the default favicon,
        // which comes in 192x192px and 256x256px and starts with `default.`.
        // These images are located in the `images/favicon/` directory.
        foreach ($this->style->hasFavicon ? [192, 256, 512] : [192, 256] as $iconSize) {
            $icons [] = [
                "src" => \sprintf(
                    "%sandroid-chrome-%dx%d.png",
                    $this->style->hasFavicon ? "" : "../favicon/default.",
                    $iconSize,
                    $iconSize
                ),
                "sizes" => "{$iconSize}x{$iconSize}",
                "type" => "image/png"
            ];
        }
        $icons = JSON::encode($icons);

        foreach (LanguageFactory::getInstance()->getLanguages() as $langauge) {
            $title = JSON::encode($langauge->get(PAGE_TITLE));

            // update manifest.json
            $manifest = <<<MANIFEST
{
    "name": {$title},
    "start_url": {$homeLocation},
    "icons": {$icons},
    "theme_color": "{$headerColor}",
    "background_color": "{$backgroundColor}",
    "display": "standalone"
}
MANIFEST;
            $manifestPath = $this->style->getAssetPath() . "manifest-{$langauge->languageID}.json";
            if (\file_exists($manifestPath) && \hash_equals(\sha1_file($manifestPath), \sha1($manifest))) {
                continue;
            }
            $writer = new AtomicWriter($manifestPath);
            $writer->write($manifest);
            $writer->flush();
            $writer->close();
        }
    }
}
