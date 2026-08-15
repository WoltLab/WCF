<?php

namespace wcf\command\style;

use wcf\data\style\Style;
use wcf\system\io\AtomicWriter;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\JSON;

/**
 * Generate then `manifest-*.json` files for a style.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class CreateManifest
{
    public function __construct(
        private readonly Style $style
    ) {}

    public function __invoke(): void
    {
        $this->style->loadVariables();
        $headerColor = \json_encode((string)$this->style->getVariable('wcfHeaderBackground', true), \JSON_THROW_ON_ERROR);
        $backgroundColor = \json_encode((string)$this->style->getVariable('wcfContentBackground', true), \JSON_THROW_ON_ERROR);

        $icons = [];
        foreach ([192, 256, 512] as $iconSize) {
            $icons[] = [
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

        $originalLanguage = WCF::getLanguage();
        try {
            foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                // To get the correct landing page url, we need to change the language.
                WCF::setLanguage($language->languageID);

                $title = JSON::encode($language->get(PAGE_TITLE));

                // update manifest.json
                $manifest = <<<MANIFEST
                {
                    "name": {$title},
                    "icons": {$icons},
                    "theme_color": {$headerColor},
                    "background_color": {$backgroundColor},
                    "display": "standalone"
                }
                MANIFEST;
                $manifestPath = $this->style->getAssetPath() . "manifest-{$language->languageID}.json";
                if (\file_exists($manifestPath) && \hash_equals(\sha1_file($manifestPath), \sha1($manifest))) {
                    continue;
                }
                $writer = new AtomicWriter($manifestPath);
                $writer->write($manifest);
                $writer->flush();
                $writer->close();
            }
        } finally {
            WCF::setLanguage($originalLanguage->languageID);
        }
    }
}
