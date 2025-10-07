<?php

namespace wcf\command\style;

use wcf\data\style\Style;
use wcf\data\style\StyleEditor;
use wcf\event\style\StyleCopied;
use wcf\system\cache\builder\StyleCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Duplicates an existing style.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class CopyStyle
{
    public function __construct(private readonly Style $style) {}

    public function __invoke(): Style
    {
        $newStyle = $this->copyStyle($this->style);
        $this->handleI18n($newStyle);
        $this->copyVariables($this->style, $newStyle);
        $this->copyAssets($this->style, $newStyle);

        EventHandler::getInstance()->fire(
            new StyleCopied($this->style, $newStyle)
        );

        return $newStyle;
    }

    private function copyStyle(Style $style): Style
    {
        return StyleEditor::create([
            'styleName' => $this->getUniqueStyleName($style),
            'templateGroupID' => $style->templateGroupID,
            'isDisabled' => 1, // newly created styles are disabled by default
            'styleDescription' => $style->styleDescription,
            'styleVersion' => $style->styleVersion,
            'styleDate' => $style->styleDate,
            'copyright' => $style->copyright,
            'license' => $style->license,
            'authorName' => $style->authorName,
            'authorURL' => $style->authorURL,
            'imagePath' => $style->imagePath,
            'coverPhotoExtension' => $style->coverPhotoExtension,
            'hasFavicon' => $style->hasFavicon,
            'hasDarkMode' => $style->hasDarkMode,
        ]);
    }

    private function handleI18n(Style $style): void
    {
        if (!\preg_match('~^wcf.style.styleDescription\d+$~', $style->styleDescription)) {
            return;
        }
        $styleDescription = 'wcf.style.styleDescription' . $style->styleID;

        // delete any phrases that were the result of an import
        $sql = "DELETE FROM wcf1_language_item
                WHERE       languageItem = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$styleDescription]);

        $sql = "INSERT INTO wcf1_language_item
                            (languageID, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID)
                SELECT      languageID, '" . $styleDescription . "', languageItemValue, 0, languageCategoryID, packageID
                FROM        wcf1_language_item
                WHERE       languageItem = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$style->styleDescription]);

        (new StyleEditor($style))->update([
            'styleDescription' => $styleDescription,
        ]);

        LanguageFactory::getInstance()->deleteLanguageCache();
    }

    private function copyVariables(Style $source, Style $destination): void
    {
        $sql = "INSERT INTO             wcf1_style_variable_value
                                        (styleID, variableID, variableValue, variableValueDarkMode)
                SELECT                  ?, variableID, variableValue, variableValueDarkMode
                FROM                    wcf1_style_variable_value
                WHERE                   styleID = ?
                ON DUPLICATE KEY UPDATE variableValue = VALUES(variableValue),
                                        variableValueDarkMode = VALUES(variableValueDarkMode)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$destination->styleID, $source->styleID]);
    }

    private function copyAssets(Style $source, Style $destination): void
    {
        foreach (['image', 'image2x'] as $imageType) {
            $image = $source->{$imageType};
            if ($image) {
                (new StyleEditor($destination))->update([
                    $imageType => \preg_replace('/^style-\d+/', 'style-' . $destination->styleID, $image),
                ]);
            }
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $source->getAssetPath(),
                \FilesystemIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $file) {
            \assert($file instanceof \SplFileInfo);

            if ($file->isDir()) {
                $relativePath = FileUtil::getRelativePath($source->getAssetPath(), $file->getPathname());
            } elseif ($file->isFile()) {
                $relativePath = FileUtil::getRelativePath($source->getAssetPath(), $file->getPath());
            } else {
                throw new \LogicException('Unreachable');
            }
            $targetFolder = $destination->getAssetPath() . $relativePath;
            FileUtil::makePath($targetFolder);
            if ($file->isFile()) {
                \copy($file->getPathname(), $targetFolder . $file->getFilename());
            }
        }

        StyleCacheBuilder::getInstance()->reset();
    }

    private function getUniqueStyleName(Style $style): string
    {
        $sql = "SELECT  styleName
                FROM    wcf1_style
                WHERE   styleName LIKE ?
                    AND styleID <> ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $style->styleName . '%',
            $style->styleID,
        ]);
        $numbers = [];
        $regEx = new Regex('\((\d+)\)$');
        while ($styleName = $statement->fetchColumn()) {
            if ($regEx->match($styleName)) {
                $matches = $regEx->getMatches();

                // check if name matches the pattern 'styleName (x)'
                if ($styleName == $styleName . ' (' . $matches[1] . ')') {
                    $numbers[] = $matches[1];
                }
            }
        }

        $number = \count($numbers) ? \max($numbers) + 1 : 2;

        return $style->styleName . ' (' . $number . ')';
    }
}
