<?php

namespace wcf\data\style;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\data\language\category\LanguageCategory;
use wcf\data\language\LanguageList;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\data\template\group\TemplateGroup;
use wcf\data\template\group\TemplateGroupAction;
use wcf\data\template\Template;
use wcf\data\template\TemplateEditor;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\StyleCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\io\Tar;
use wcf\system\io\TarWriter;
use wcf\system\language\LanguageFactory;
use wcf\system\package\PackageArchive;
use wcf\system\Regex;
use wcf\system\style\exception\FontDownloadFailed;
use wcf\system\style\FontManager;
use wcf\system\style\StyleCompiler;
use wcf\system\style\StyleHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\FileUtil;
use wcf\util\ImageUtil;
use wcf\util\StringUtil;
use wcf\util\XML;
use wcf\util\XMLWriter;

/**
 * Provides functions to edit, import, export and delete a style.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Style   getDecoratedObject()
 * @mixin   Style
 */
final class StyleEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    const EXCLUDE_WCF_VERSION = '7.0.0 Alpha 1';

    const INFO_FILE = 'style.xml';

    const VALID_IMAGE_EXTENSIONS = ['gif', 'jpg', 'jpeg', 'png', 'svg', 'xml', 'json', 'webp', 'ico'];

    /**
     * @inheritDoc
     */
    protected static $baseClass = Style::class;

    /**
     * @inheritDoc
     */
    public function update(array $parameters = [])
    {
        if (isset($parameters['variablesDarkMode'])) {
            throw new \InvalidArgumentException(
                "Cannot provide variables when updating a style, use `setVariables()` instead."
            );
        }

        $inputVariables = $parameters['variables'] ?? null;
        unset($parameters['variables']);

        parent::update($parameters);

        if ($inputVariables !== null) {
            $variables = $variablesDarkMode = [];
            $prefixLength = \strlen(Style::DARK_MODE_PREFIX);
            foreach ($inputVariables as $variableName => $variableValue) {
                if (\str_starts_with($variableName, Style::DARK_MODE_PREFIX)) {
                    $variableName = \substr($variableName, $prefixLength);
                    if ($variableName === 'individualScssDarkMode') {
                        continue;
                    }

                    $variablesDarkMode[$variableName] = $variableValue;
                } else {
                    $variables[$variableName] = $variableValue;
                }
            }

            $this->setVariables($variables, $variablesDarkMode);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        $sql = "DELETE FROM wcf1_style
                WHERE       styleID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->styleID]);

        // delete CSS files
        StyleHandler::getInstance()->resetStylesheet($this->getDecoratedObject());

        // remove custom images
        if ($this->imagePath && $this->imagePath != 'images/') {
            $this->removeDirectory($this->imagePath);
        }

        // delete preview image
        if ($this->image) {
            @\unlink(WCF_DIR . 'images/' . $this->image);
        }

        // delete language items
        $sql = "DELETE FROM wcf1_language_item
                WHERE       languageItem = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(['wcf.style.styleDescription' . $this->styleID]);
    }

    /**
     * @inheritDoc
     */
    public static function deleteAll(array $objectIDs = [])
    {
        $styleList = new StyleList();
        $styleList->decoratorClassName = static::class;
        $styleList->setObjectIDs($objectIDs);
        $styleList->readObjects();

        foreach ($styleList as $style) {
            \assert($style instanceof self);
            $style->delete();
        }
    }

    /**
     * Recursively removes a directory and all it's contents.
     *
     * @since 5.4
     */
    private function removeDirectory(string $pathComponent)
    {
        $dir = WCF_DIR . $pathComponent;
        if (\is_dir($dir)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $path) {
                if ($path->isDir()) {
                    @\rmdir($path);
                } else {
                    @\unlink($path);
                }
            }

            @\rmdir($dir);
        }
    }

    /**
     * Sets this style as default style.
     */
    public function setAsDefault()
    {
        // remove old default
        $sql = "UPDATE  wcf1_style
                SET     isDefault = ?
                WHERE   isDefault = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([0, 1]);

        // set new default
        $this->update([
            'isDefault' => 1,
            'isDisabled' => 0,
        ]);

        self::resetCache();
    }

    /**
     * Deletes the style's default cover photo.
     */
    public function deleteCoverPhoto()
    {
        if ($this->coverPhotoExtension) {
            @\unlink(WCF_DIR . 'images/coverPhotos/' . $this->styleID . '.' . $this->coverPhotoExtension);

            $this->update([
                'coverPhotoExtension' => '',
            ]);
        }
    }

    /**
     * Returns the list of variables that exist, but have no explicit values for this style.
     *
     * @return      string[]
     */
    public function getImplicitVariables()
    {
        $sql = "SELECT      variable.variableName
                FROM        wcf1_style_variable variable
                LEFT JOIN   wcf1_style_variable_value variable_value
                ON          variable_value.variableID = variable.variableID
                        AND variable_value.styleID = ?
                WHERE       variable.variableName LIKE ?
                        AND variable_value.variableValue IS NULL";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->styleID,
            'wcf%',
        ]);
        $variableNames = [];
        while ($variableName = $statement->fetchColumn()) {
            $variableNames[] = $variableName;
        }

        return $variableNames;
    }

    /**
     * @return null|bool
     * @since 5.4
     */
    public function createCoverPhotoVariant(?string $sourceLocation = null)
    {
        if ($sourceLocation === null) {
            $sourceLocation = $this->getCoverPhotoLocation(false);
        }

        $outputFilenameWithoutExtension = \preg_replace('~\.[a-z]+$~', '', $sourceLocation);

        return ImageUtil::createWebpVariant($sourceLocation, $outputFilenameWithoutExtension);
    }

    /**
     * Reads the data of a style exchange format file.
     *
     * @param Tar $tar
     * @return  array
     * @throws  SystemException
     */
    public static function readStyleData(Tar $tar)
    {
        // search style.xml
        $index = $tar->getIndexByFilename(self::INFO_FILE);
        if ($index === false) {
            throw new SystemException("unable to find required file '" . self::INFO_FILE . "' in style archive");
        }

        // open style.xml
        $xml = new XML();
        $xml->loadXML(self::INFO_FILE, $tar->extractToString($index));
        $xpath = $xml->xpath();

        $data = [
            'name' => '',
            'description' => [],
            'version' => '',
            'image' => '',
            'image2x' => '',
            'copyright' => '',
            'default' => false,
            'license' => '',
            'authorName' => '',
            'authorURL' => '',
            'templates' => '',
            'images' => '',
            'coverPhoto' => '',
            'variables' => '',
            'variablesDarkMode' => '',
            'date' => '0000-00-00',
            'imagesPath' => '',
            'packageName' => '',
            'hasDarkMode' => false,
        ];

        $categories = $xpath->query('/ns:style/*');
        foreach ($categories as $category) {
            switch ($category->tagName) {
                case 'author':
                    $elements = $xpath->query('child::*', $category);
                    foreach ($elements as $element) {
                        switch ($element->tagName) {
                            case 'authorname':
                                $data['authorName'] = $element->nodeValue;
                                break;

                            case 'authorurl':
                                $data['authorURL'] = $element->nodeValue;
                                break;
                        }
                    }
                    break;

                case 'files':
                    $elements = $xpath->query('child::*', $category);

                    /** @var \DOMElement $element */
                    foreach ($elements as $element) {
                        $data[$element->tagName] = $element->nodeValue;
                        if ($element->hasAttribute('path')) {
                            $data[$element->tagName . 'Path'] = $element->getAttribute('path');
                        }
                    }
                    break;

                case 'general':
                    $elements = $xpath->query('child::*', $category);

                    /** @var \DOMElement $element */
                    foreach ($elements as $element) {
                        switch ($element->tagName) {
                            case 'date':
                                DateUtil::validateDate($element->nodeValue);

                                $data['date'] = $element->nodeValue;
                                break;

                            case 'default':
                            case 'hasDarkMode':
                                $data[$element->tagName] = true;
                                break;

                            case 'description':
                                if ($element->hasAttribute('language')) {
                                    $data['description'][$element->getAttribute('language')] = $element->nodeValue;
                                }
                                break;

                            case 'stylename':
                                $data['name'] = $element->nodeValue;
                                break;

                            case 'packageName':
                                $data['packageName'] = $element->nodeValue;
                                break;

                            case 'version':
                                if (!Package::isValidVersion($element->nodeValue)) {
                                    throw new SystemException("style version '" . $element->nodeValue . "' is invalid");
                                }

                                $data['version'] = $element->nodeValue;
                                break;

                            case 'copyright':
                            case 'image':
                            case 'image2x':
                            case 'license':
                            case 'coverPhoto':
                                $data[$element->tagName] = $element->nodeValue;
                                break;
                        }
                    }
                    break;
            }
        }

        if (empty($data['name'])) {
            throw new SystemException("required tag 'stylename' is missing in '" . self::INFO_FILE . "'");
        }
        if (empty($data['variables'])) {
            throw new SystemException("required tag 'variables' is missing in '" . self::INFO_FILE . "'");
        }

        $index = $tar->getIndexByFilename($data['variables']);
        if ($index === false) {
            throw new SystemException("unable to find required file '" . $data['variables'] . "' in style archive");
        }
        $data['variables'] = self::readVariablesData($data['variables'], $tar->extractToString($index));

        if ($data['variablesDarkMode']) {
            $index = $tar->getIndexByFilename($data['variablesDarkMode']);
            if ($index === false) {
                throw new SystemException("unable to find required file '" . $data['variablesDarkMode'] . "' in style archive");
            }
            $data['variablesDarkMode'] = self::readVariablesData($data['variablesDarkMode'], $tar->extractToString($index));
        }

        return $data;
    }

    /**
     * Reads the data of a variables.xml file.
     *
     * @param string $filename
     * @param string $content
     * @return  array
     */
    public static function readVariablesData($filename, $content)
    {
        // open variables.xml
        $xml = new XML();
        $xml->loadXML($filename, $content);
        $variables = $xml->xpath()->query('/ns:variables/ns:variable');

        $data = [];

        /** @var \DOMElement $variable */
        foreach ($variables as $variable) {
            $data[$variable->getAttribute('name')] = $variable->nodeValue;
        }

        return $data;
    }

    /**
     * Returns the data of a style exchange format file.
     *
     * @param string $filename
     * @return  array
     */
    public static function getStyleData($filename)
    {
        // open file
        $tar = new Tar($filename);

        // get style data
        $data = self::readStyleData($tar);

        // export preview image to temporary location
        if (!empty($data['image'])) {
            $i = $tar->getIndexByFilename($data['image']);
            if ($i !== false) {
                $path = FileUtil::getTemporaryFilename('stylePreview_', $data['image'], WCF_DIR . 'tmp/');
                $data['image'] = \basename($path);
                $tar->extract($i, $path);
            }
        }

        $tar->close();

        return $data;
    }

    /**
     * Imports a style.
     *
     * @param string $filename
     * @param int $packageID
     * @param StyleEditor $style
     * @param bool $skipFontDownload
     * @return  StyleEditor
     */
    public static function import($filename, $packageID = 1, ?self $style = null, $skipFontDownload = false)
    {
        // open file
        $tar = new Tar($filename);

        // get style data
        $data = self::readStyleData($tar);

        $styleData = [
            'styleName' => $data['name'],
            'variables' => $data['variables'],
            'variablesDarkMode' => $data['variablesDarkMode'],
            'styleVersion' => $data['version'],
            'styleDate' => $data['date'],
            'copyright' => $data['copyright'],
            'license' => $data['license'],
            'authorName' => $data['authorName'],
            'authorURL' => $data['authorURL'],
            'packageName' => $data['packageName'],
            'hasDarkMode' => $data['hasDarkMode'] ? 1 : 0,
        ];

        // check if there is an untainted style with the same package name
        if ($style === null && !empty($styleData['packageName'])) {
            $style = StyleHandler::getInstance()->getStyleByName($styleData['packageName'], true);
        }

        // handle templates
        if (!empty($data['templates'])) {
            $templateGroupFolderName = '';
            if ($style !== null && $style->templateGroupID) {
                $templateGroupFolderName = (new TemplateGroup($style->templateGroupID))->templateGroupFolderName;
                $styleData['templateGroupID'] = $style->templateGroupID;
            }

            if (empty($templateGroupFolderName)) {
                // create template group
                $templateGroupName = $originalTemplateGroupName = $data['name'];
                $templateGroupFolderName = \preg_replace('/[^a-z0-9_-]/i', '', $templateGroupName);
                if (empty($templateGroupFolderName)) {
                    $templateGroupFolderName = 'generic' . \mb_substr(StringUtil::getRandomID(), 0, 8);
                }
                $originalTemplateGroupFolderName = $templateGroupFolderName;

                // get unique template group name
                $i = 1;
                while (true) {
                    $sql = "SELECT  COUNT(*)
                            FROM    wcf1_template_group
                            WHERE   templateGroupName = ?";
                    $statement = WCF::getDB()->prepare($sql);
                    $statement->execute([$templateGroupName]);
                    if (!$statement->fetchSingleColumn()) {
                        break;
                    }
                    $templateGroupName = $originalTemplateGroupName . '_' . $i;
                    $i++;
                }

                // get unique folder name
                $i = 1;
                while (true) {
                    $sql = "SELECT  COUNT(*)
                            FROM    wcf1_template_group
                            WHERE   templateGroupFolderName = ?";
                    $statement = WCF::getDB()->prepare($sql);
                    $statement->execute([
                        FileUtil::addTrailingSlash($templateGroupFolderName),
                    ]);
                    if (!$statement->fetchSingleColumn()) {
                        break;
                    }
                    $templateGroupFolderName = $originalTemplateGroupFolderName . '_' . $i;
                    $i++;
                }

                $templateGroupAction = new TemplateGroupAction([], 'create', [
                    'data' => [
                        'templateGroupName' => $templateGroupName,
                        'templateGroupFolderName' => FileUtil::addTrailingSlash($templateGroupFolderName),
                    ],
                ]);
                $returnValues = $templateGroupAction->executeAction();
                $styleData['templateGroupID'] = $returnValues['returnValues']->templateGroupID;
            }

            // import templates
            $index = $tar->getIndexByFilename($data['templates']);
            if ($index !== false) {
                // extract templates tar
                $destination = FileUtil::getTemporaryFilename('templates_');
                $tar->extract($index, $destination);

                // open templates tar and group templates by package
                $templatesTar = new Tar($destination);
                $contentList = $templatesTar->getContentList();
                $packageToTemplates = [];
                foreach ($contentList as $val) {
                    if ($val['type'] == 'file') {
                        $folders = \explode('/', $val['filename']);
                        $packageName = \array_shift($folders);
                        if (!isset($packageToTemplates[$packageName])) {
                            $packageToTemplates[$packageName] = [];
                        }
                        $packageToTemplates[$packageName][] = [
                            'index' => $val['index'],
                            'filename' => \implode('/', $folders),
                        ];
                    }
                }

                $knownTemplates = [];
                if ($style !== null && $style->templateGroupID) {
                    $sql = "SELECT  *
                            FROM    wcf1_template
                            WHERE   templateGroupID = ?";
                    $statement = WCF::getDB()->prepare($sql);
                    $statement->execute([$style->templateGroupID]);
                    foreach ($statement->fetchObjects(Template::class) as $template) {
                        $knownTemplates[$template->application . '-' . $template->templateName] = new TemplateEditor($template);
                    }
                }

                // copy templates
                foreach ($packageToTemplates as $package => $templates) {
                    // try to find package
                    $sql = "SELECT  *
                            FROM    wcf1_package
                            WHERE   package = ?
                                AND isApplication = ?";
                    $statement = WCF::getDB()->prepare($sql);
                    $statement->execute([
                        $package,
                        1,
                    ]);
                    while ($row = $statement->fetchArray()) {
                        // get template path
                        $templatesDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR . $row['packageDir']) . 'templates/' . $templateGroupFolderName);

                        // create template path
                        if (!\file_exists($templatesDir)) {
                            @\mkdir($templatesDir, 0777);
                            FileUtil::makeWritable($templatesDir);
                        }

                        // copy templates
                        foreach ($templates as $template) {
                            if (!\str_ends_with($template['filename'], '.tpl')) {
                                continue;
                            }

                            $templatesTar->extract($template['index'], $templatesDir . $template['filename']);

                            $templateName = \str_replace('.tpl', '', $template['filename']);

                            if (isset($knownTemplates[Package::getAbbreviation($package) . '-' . $templateName])) {
                                $knownTemplates[Package::getAbbreviation($package) . '-' . $templateName]->update([
                                    'lastModificationTime' => TIME_NOW,
                                ]);
                            } else {
                                TemplateEditor::create([
                                    'application' => Package::getAbbreviation($package),
                                    'packageID' => $row['packageID'],
                                    'templateName' => $templateName,
                                    'templateGroupID' => $styleData['templateGroupID'],
                                ]);
                            }
                        }
                    }
                }

                // delete tmp file
                $templatesTar->close();
                @\unlink($destination);
            }
        }

        $duplicateLogo = false;
        // duplicate logo if logo matches mobile logo
        if (
            !empty($styleData['variables']['pageLogo'])
            && !empty($styleData['variables']['pageLogoMobile'])
            && $styleData['variables']['pageLogo'] == $styleData['variables']['pageLogoMobile']
        ) {
            $styleData['variables']['pageLogoMobile'] = 'm-' . \basename($styleData['variables']['pageLogo']);
            $duplicateLogo = true;
        }

        // save style
        if ($style === null) {
            $styleData['packageID'] = $packageID;
            $style = new self(self::create($styleData));

            // handle descriptions
            if (!empty($data['description'])) {
                self::saveLocalizedDescriptions($style, $data['description']);
                LanguageFactory::getInstance()->deleteLanguageCache();
            }

            if ($data['default']) {
                $style->setAsDefault();
            }
        } else {
            unset($styleData['styleName']);

            $variables = $style->getVariables();
            if (!isset($styleData['variables']['individualScss'])) {
                $styleData['variables']['individualScss'] = '';
            }
            if (!isset($styleData['variables']['individualScssDarkMode'])) {
                $styleData['variables']['individualScssDarkMode'] = '';
            }
            if (!isset($styleData['variables']['overrideScss'])) {
                $styleData['variables']['overrideScss'] = '';
            }

            $individualScss = Style::splitLessVariables($variables['individualScss']);
            $variables['individualScss'] = Style::joinLessVariables(
                $styleData['variables']['individualScss'],
                $individualScss['custom']
            );

            $individualScssDarkMode = Style::splitLessVariables($variables['individualScssDarkMode']);
            $variables['individualScssDarkMode'] = Style::joinLessVariables(
                $styleData['variables']['individualScssDarkMode'],
                $individualScssDarkMode['custom']
            );

            $overrideScss = Style::splitLessVariables($variables['overrideScss']);
            $variables['overrideScss'] = Style::joinLessVariables(
                $styleData['variables']['overrideScss'],
                $overrideScss['custom']
            );

            $styleData['variables'] = $variables;
            unset($styleData['variablesDarkMode']);

            $style->update($styleData);
        }

        // import images
        if (!empty($data['images'])) {
            $index = $tar->getIndexByFilename($data['images']);
            if ($index !== false) {
                // extract images tar
                $destination = FileUtil::getTemporaryFilename('images_');
                $tar->extract($index, $destination);

                // open images tar
                $imagesTar = new Tar($destination);
                $contentList = $imagesTar->getContentList();
                foreach ($contentList as $key => $val) {
                    if ($val['type'] == 'file') {
                        $path = FileUtil::getRealPath($val['filename']);
                        $fileExtension = \pathinfo($path, \PATHINFO_EXTENSION);

                        if (!\in_array($fileExtension, self::VALID_IMAGE_EXTENSIONS)) {
                            continue;
                        }

                        if (\str_contains($path, '../')) {
                            continue;
                        }

                        $targetFile = FileUtil::getRealPath($style->getAssetPath() . $path);
                        if (\str_contains(FileUtil::getRelativePath($style->getAssetPath(), $targetFile), '../')) {
                            continue;
                        }

                        // Check whether a file within the custom/ directory would be overwritten.
                        // Skip the extraction in this case, to preserve administrator changes. A style author
                        // can opt to use a different (versioned) file name if they *need* to update the image.
                        if (\str_starts_with($targetFile, $style->getAssetPath() . 'custom/')) {
                            if (\file_exists($targetFile)) {
                                continue;
                            }
                        }

                        // duplicate pageLogo for mobile version
                        if ($duplicateLogo && $val['filename'] == $styleData['variables']['pageLogo']) {
                            $imagesTar->extract($key, $style->getAssetPath() . 'm-' . \basename($targetFile));
                        }

                        $imagesTar->extract($key, $targetFile);
                        FileUtil::makeWritable($targetFile);

                        if (\preg_match('/^favicon\-template\.(png|jpg|gif)$/', \basename($targetFile))) {
                            $style->update([
                                'hasFavicon' => 1,
                            ]);
                        }
                    }
                }

                // delete tmp file
                $imagesTar->close();
                @\unlink($destination);
            }
        }

        // import preview image
        foreach (['image', 'image2x'] as $type) {
            if (!empty($data[$type])) {
                $fileExtension = \pathinfo($data[$type], \PATHINFO_EXTENSION);
                if (!\in_array($fileExtension, self::VALID_IMAGE_EXTENSIONS)) {
                    continue;
                }

                $index = $tar->getIndexByFilename($data[$type]);
                if ($index !== false) {
                    $filename = $style->getAssetPath() . 'stylePreview' . ($type === 'image2x' ? '@2x' : '') . '.' . $fileExtension;
                    $tar->extract($index, $filename);
                    FileUtil::makeWritable($filename);

                    if (\file_exists($filename)) {
                        try {
                            if (($imageData = \getimagesize($filename)) !== false) {
                                switch ($imageData[2]) {
                                    case \IMAGETYPE_PNG:
                                    case \IMAGETYPE_JPEG:
                                    case \IMAGETYPE_GIF:
                                        $style->update([$type => 'style-' . $style->styleID . '/stylePreview' . ($type === 'image2x' ? '@2x' : '') . '.' . $fileExtension]);
                                }
                            }
                        } catch (SystemException $e) {
                            // broken image
                        }
                    }
                }
            }
        }

        // import cover photo
        if (!empty($data['coverPhoto'])) {
            $fileExtension = \pathinfo($data['coverPhoto'], \PATHINFO_EXTENSION);
            $index = $tar->getIndexByFilename($data['coverPhoto']);
            if ($index !== false && \in_array($fileExtension, self::VALID_IMAGE_EXTENSIONS)) {
                $coverPhoto = "{$style->getAssetPath()}coverPhoto.{$fileExtension}";
                $tar->extract($index, $coverPhoto);
                FileUtil::makeWritable($coverPhoto);

                if (\file_exists($coverPhoto)) {
                    try {
                        if (($imageData = \getimagesize($coverPhoto)) !== false) {
                            switch ($imageData[2]) {
                                case \IMAGETYPE_PNG:
                                case \IMAGETYPE_JPEG:
                                case \IMAGETYPE_GIF:
                                    $style->update(['coverPhotoExtension' => $fileExtension]);

                                    // Reload the style editor to include the cover photo.
                                    $style = new self(new Style($style->styleID));
                                    $style->createCoverPhotoVariant();
                                    break;
                            }
                        }
                    } catch (SystemException $e) {
                        // broken image
                    }
                }
            }
        }

        if (!$skipFontDownload) {
            // download google fonts
            $fontManager = FontManager::getInstance();
            $style->loadVariables();
            $family = $style->getVariable('wcfFontFamilyGoogle');
            try {
                $fontManager->downloadFamily($family);
            } catch (FontDownloadFailed $e) {
                // ignore
            }
        }

        $tar->close();

        return $style;
    }

    /**
     * Saves localized style descriptions.
     *
     * @param StyleEditor $styleEditor
     * @param string[] $descriptions
     */
    protected static function saveLocalizedDescriptions(self $styleEditor, array $descriptions)
    {
        // localize package information
        $sql = "REPLACE INTO    wcf1_language_item
                                (languageID, languageItem, languageItemValue, languageCategoryID, packageID)
                VALUES          (?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);

        // get language list
        $languageList = new LanguageList();
        $languageList->readObjects();

        // workaround for WCFSetup
        if (!PACKAGE_ID) {
            $sql = "SELECT  *
                    FROM    wcf1_language_category
                    WHERE   languageCategory = ?";
            $statement2 = WCF::getDB()->prepare($sql);
            $statement2->execute(['wcf.style']);
            $languageCategory = $statement2->fetchObject(LanguageCategory::class);
        } else {
            $languageCategory = LanguageFactory::getInstance()->getCategory('wcf.style');
        }

        foreach ($languageList as $language) {
            if (isset($descriptions[$language->languageCode])) {
                $statement->execute([
                    $language->languageID,
                    'wcf.style.styleDescription' . $styleEditor->styleID,
                    $descriptions[$language->languageCode],
                    $languageCategory->languageCategoryID,
                    $styleEditor->packageID,
                ]);
            }
        }

        $styleEditor->update([
            'styleDescription' => 'wcf.style.styleDescription' . $styleEditor->styleID,
        ]);
    }

    /**
     * Returns available location path.
     *
     * @param string $location
     * @return  string
     */
    protected static function getFileLocation($location)
    {
        $location = FileUtil::removeLeadingSlash(FileUtil::removeTrailingSlash($location));
        $location = WCF_DIR . $location;

        $index = null;
        do {
            $directory = $location . ($index === null ? '' : $index);
            if (!\is_dir($directory)) {
                @\mkdir($directory, 0777, true);
                FileUtil::makeWritable($directory);

                return FileUtil::addTrailingSlash($directory);
            }

            $index = ($index === null ? 2 : ($index + 1));
        } while (true);

        // this should never happen
        throw new \LogicException();
    }

    /**
     * Exports this style.
     *
     * @param bool $templates
     * @param bool $images
     * @param string $packageName
     */
    public function export($templates = false, $images = false, $packageName = '')
    {
        // create style tar
        $styleTarName = FileUtil::getTemporaryFilename('style_', '.tgz');
        $styleTar = new TarWriter($styleTarName, true);

        // append style preview image
        if ($this->image && @\file_exists(WCF_DIR . 'images/' . $this->image)) {
            $styleTar->add(
                WCF_DIR . 'images/' . $this->image,
                '',
                FileUtil::addTrailingSlash(\dirname(WCF_DIR . 'images/' . $this->image))
            );
        }
        if ($this->image2x && @\file_exists(WCF_DIR . 'images/' . $this->image2x)) {
            $styleTar->add(
                WCF_DIR . 'images/' . $this->image2x,
                '',
                FileUtil::addTrailingSlash(\dirname(WCF_DIR . 'images/' . $this->image2x))
            );
        }

        // append cover photo
        $coverPhoto = $this->coverPhotoExtension ? $this->getCoverPhotoLocation(false) : '';
        if ($coverPhoto && @\file_exists($coverPhoto)) {
            $styleTar->add($coverPhoto, '', FileUtil::addTrailingSlash(\dirname($coverPhoto)));
        }

        // fetch style description
        $sql = "SELECT      language.languageCode, language_item.languageItemValue
                FROM        wcf1_language_item language_item
                LEFT JOIN   wcf1_language language
                ON          language.languageID = language_item.languageID
                WHERE       language_item.languageItem = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->styleDescription]);
        $styleDescriptions = $statement->fetchMap('languageCode', 'languageItemValue');

        // create style info file
        $xml = new XMLWriter();
        $xml->beginDocument(
            'style',
            'http://www.woltlab.com',
            'http://www.woltlab.com/XSD/6.0/style.xsd'
        );

        // general block
        $xml->startElement('general');
        $xml->writeElement('stylename', $this->styleName);
        $xml->writeElement('packageName', $this->packageName);

        // style description
        foreach ($styleDescriptions as $languageCode => $value) {
            $xml->writeElement('description', $value, ['language' => $languageCode]);
        }

        $xml->writeElement('date', $this->styleDate);
        $xml->writeElement('version', $this->styleVersion);
        if ($this->image) {
            $xml->writeElement('image', \basename($this->image));
        }
        if ($this->image2x) {
            $xml->writeElement('image2x', \basename($this->image2x));
        }
        if ($coverPhoto) {
            $xml->writeElement('coverPhoto', \basename(FileUtil::unifyDirSeparator($coverPhoto)));
        }
        if ($this->copyright) {
            $xml->writeElement('copyright', $this->copyright);
        }
        if ($this->license) {
            $xml->writeElement('license', $this->license);
        }
        if ($this->hasDarkMode) {
            $xml->writeElement('hasDarkMode', 1);
        }
        $xml->endElement();

        // author block
        $xml->startElement('author');
        $xml->writeElement('authorname', $this->authorName);
        if ($this->authorURL) {
            $xml->writeElement('authorurl', $this->authorURL);
        }
        $xml->endElement();

        // files block
        $xml->startElement('files');
        $xml->writeElement('variables', 'variables.xml');
        if ($this->hasDarkMode) {
            $xml->writeElement('variablesDarkMode', 'variables_dark.xml');
        }
        if ($templates) {
            $xml->writeElement('templates', 'templates.tar');
        }
        if ($images) {
            $xml->writeElement('images', 'images.tar', ['path' => $this->imagePath]);
        }
        $xml->endElement();

        // append style info file to style tar
        $styleTar->addString(self::INFO_FILE, $xml->endDocument());

        $xml->beginDocument(
            'variables',
            'http://www.woltlab.com',
            'http://www.woltlab.com/XSD/6.0/styleVariables.xsd'
        );

        $sql = "SELECT      variable.variableName, value.variableValue
                FROM        wcf1_style_variable_value value
                LEFT JOIN   wcf1_style_variable variable
                ON          variable.variableID = value.variableID
                WHERE       value.styleID = ?
                        AND value.variableValue IS NOT NULL";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->styleID]);
        while ($row = $statement->fetchArray()) {
            $xml->writeElement('variable', $row['variableValue'], ['name' => $row['variableName']]);
        }

        $styleTar->addString('variables.xml', $xml->endDocument());

        if ($this->hasDarkMode) {
            $xml->beginDocument(
                'variables',
                'http://www.woltlab.com',
                'http://www.woltlab.com/XSD/6.0/styleVariables.xsd'
            );

            $sql = "SELECT      variable.variableName, value.variableValueDarkMode
                    FROM        wcf1_style_variable_value value
                    LEFT JOIN   wcf1_style_variable variable
                    ON          variable.variableID = value.variableID
                    WHERE       value.styleID = ?
                            AND value.variableValueDarkMode IS NOT NULL";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->styleID]);
            while ($row = $statement->fetchArray()) {
                if ($row['variableName'] === 'individualScssDarkMode') {
                    continue;
                }

                $xml->writeElement('variable', $row['variableValueDarkMode'], ['name' => $row['variableName']]);
            }

            $styleTar->addString('variables_dark.xml', $xml->endDocument());
        }

        if ($templates && $this->templateGroupID) {
            $templateGroup = new TemplateGroup($this->templateGroupID);

            // create templates tar
            $templatesTarName = FileUtil::getTemporaryFilename('templates', '.tar');
            $templatesTar = new TarWriter($templatesTarName);
            FileUtil::makeWritable($templatesTarName);

            // append templates to tar
            // get templates
            $sql = "SELECT      template.*, package.package
                    FROM        wcf1_template template
                    LEFT JOIN   wcf1_package package
                    ON          package.packageID = template.packageID
                    WHERE       template.templateGroupID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->templateGroupID]);
            while ($row = $statement->fetchArray()) {
                $packageDir = 'com.woltlab.wcf';
                $package = null;

                if (Template::isSystemCritical($row['templateName'])) {
                    continue;
                }

                if ($row['application'] != 'wcf') {
                    $application = ApplicationHandler::getInstance()->getApplication($row['application']);
                    $package = PackageCache::getInstance()->getPackage($application->packageID);
                    $packageDir = $package->package;
                } else {
                    $application = ApplicationHandler::getInstance()->getWCF();
                    $package = PackageCache::getInstance()->getPackage($application->packageID);
                }

                $filename = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR . $package->packageDir . 'templates/' . $templateGroup->templateGroupFolderName)) . $row['templateName'] . '.tpl';
                $templatesTar->add($filename, $packageDir, \dirname($filename));
            }

            // append templates tar to style tar
            $templatesTar->create();
            $styleTar->add($templatesTarName, 'templates.tar', $templatesTarName);
            @\unlink($templatesTarName);
        }

        if ($images) {
            // create images tar
            $imagesTarName = FileUtil::getTemporaryFilename('images_', '.tar');
            $imagesTar = new TarWriter($imagesTarName);
            FileUtil::makeWritable($imagesTarName);

            $regEx = new Regex('^([a-zA-Z0-9_-]+\.)+(jpg|jpeg|gif|png|svg|ico|json|xml|txt|webp)$', Regex::CASE_INSENSITIVE);
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $this->getAssetPath(),
                    \FilesystemIterator::SKIP_DOTS
                ),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($iterator as $file) {
                /** @var \SplFileInfo $file */
                if (!$file->isFile()) {
                    continue;
                }
                if (!$regEx->match($file->getBasename())) {
                    continue;
                }

                // Skip preview images and cover photos.
                if (
                    $this->image
                    && FileUtil::unifyDirSeparator($file->getPathname()) === FileUtil::unifyDirSeparator(WCF_DIR . 'images/' . $this->image)
                ) {
                    continue;
                }
                if (
                    $this->image2x
                    && FileUtil::unifyDirSeparator($file->getPathname()) === FileUtil::unifyDirSeparator(WCF_DIR . 'images/' . $this->image2x)
                ) {
                    continue;
                }
                if (
                    $coverPhoto
                    && FileUtil::unifyDirSeparator($file->getPathname()) === FileUtil::unifyDirSeparator($coverPhoto)
                ) {
                    continue;
                }

                $imagesTar->add($file->getPathName(), '', $this->getAssetPath());
            }
            // append images tar to style tar
            $imagesTar->create();
            $styleTar->add($imagesTarName, 'images.tar', $imagesTarName);
            @\unlink($imagesTarName);
        }

        // output file content
        $styleTar->create();

        // export as style package
        if (empty($packageName)) {
            \readfile($styleTarName);
        } else {
            // export as package

            // create package tar
            $packageTarName = FileUtil::getTemporaryFilename('package_', '.tar.gz');
            $packageTar = new TarWriter($packageTarName, true);

            // append style tar
            $styleTarName = FileUtil::unifyDirSeparator($styleTarName);
            $packageTar->add($styleTarName, '', FileUtil::addTrailingSlash(\dirname($styleTarName)));

            // create package.xml
            $xml->beginDocument(
                'package',
                'http://www.woltlab.com',
                'http://www.woltlab.com/XSD/6.0/package.xsd',
                ['name' => $packageName]
            );

            $xml->startElement('packageinformation');
            $xml->writeElement('packagename', $this->styleName);

            // description
            foreach ($styleDescriptions as $languageCode => $value) {
                // The description of a style is effectively stored in a TEXT column
                // but packages use a VARCHAR(255) to store it.
                $value = \mb_substr($value, 0, 255);

                $xml->writeElement('packagedescription', $value, ['language' => $languageCode]);
            }

            $xml->writeElement('version', $this->styleVersion);
            $xml->writeElement('date', $this->styleDate);
            $xml->endElement();

            $xml->startElement('authorinformation');
            $xml->writeElement('author', $this->authorName);
            if ($this->authorURL) {
                $xml->writeElement('authorurl', $this->authorURL);
            }
            $xml->endElement();

            $xml->startElement('requiredpackages');
            $xml->writeElement(
                'requiredpackage',
                'com.woltlab.wcf',
                ['minversion' => PackageCache::getInstance()->getPackageByIdentifier('com.woltlab.wcf')->packageVersion]
            );
            $xml->endElement();

            $xml->startElement('excludedpackages');
            $xml->writeElement('excludedpackage', 'com.woltlab.wcf', ['version' => self::EXCLUDE_WCF_VERSION]);
            $xml->endElement();

            $xml->startElement('instructions', ['type' => 'install']);
            $xml->writeElement('instruction', \basename($styleTarName), ['type' => 'style']);
            $xml->endElement();

            // append package info file to package tar
            $packageTar->addString(PackageArchive::INFO_FILE, $xml->endDocument());

            $packageTar->create();
            \readfile($packageTarName);
            @\unlink($packageTarName);
        }

        @\unlink($styleTarName);
    }

    /**
     * Sets the variables of a style.
     *
     * @param string[] $variables
     * @param string[] $variablesDarkMode
     */
    public function setVariables(array $variables, array $variablesDarkMode): void
    {
        $sql = "SELECT  variableID, variableName
                FROM    wcf1_style_variable";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $styleVariables = $statement->fetchMap('variableID', 'variableName');

        $variables = \array_filter($variables, static function (string $key) use ($styleVariables) {
            return \in_array($key, $styleVariables, true);
        }, \ARRAY_FILTER_USE_KEY);

        $variablesDarkMode = \array_filter($variablesDarkMode, static function (string $key) use ($styleVariables) {
            return \in_array($key, $styleVariables, true);
        }, \ARRAY_FILTER_USE_KEY);

        $sql = "INSERT INTO             wcf1_style_variable_value
                                        (styleID, variableID, variableValue, variableValueDarkMode)
                VALUES                  (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE variableValue = VALUES(variableValue),
                                        variableValueDarkMode = VALUES(variableValueDarkMode)";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($styleVariables as $variableID => $variableName) {
            $statement->execute([
                $this->styleID,
                $variableID,
                $variables[$variableName] ?? null,
                $variablesDarkMode[$variableName] ?? null,
            ]);
        }

        StyleHandler::getInstance()->resetStylesheet($this->getDecoratedObject());
    }

    /**
     * Writes the style-*.css file.
     */
    public function writeStyleFile()
    {
        StyleCompiler::getInstance()->compile($this->getDecoratedObject());
    }

    /**
     * @inheritDoc
     * @return  Style
     */
    public static function create(array $parameters = [])
    {
        $variables = [];
        if (isset($parameters['variables'])) {
            $variables = $parameters['variables'];
            unset($parameters['variables']);
        }
        $variablesDarkMode = [];
        if (isset($parameters['variablesDarkMode'])) {
            if (\is_array($parameters['variablesDarkMode'])) {
                $variablesDarkMode = $parameters['variablesDarkMode'];
            }

            unset($parameters['variablesDarkMode']);
        }

        // default values
        if (!isset($parameters['packageID'])) {
            $parameters['packageID'] = 1;
        }
        if (!isset($parameters['styleDate'])) {
            $parameters['styleDate'] = \gmdate('Y-m-d', TIME_NOW);
        }

        // check if no default style is defined
        $sql = "SELECT  styleID
                FROM    wcf1_style
                WHERE   isDefault = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([1]);
        $row = $statement->fetchArray();

        // no default style exists
        if ($row === false) {
            $parameters['isDefault'] = 1;
        }

        if ($variablesDarkMode !== []) {
            $parameters['hasDarkMode'] = 1;
        }

        /** @var Style $style */
        $style = parent::create($parameters);
        $styleEditor = new self($style);

        // create asset path
        FileUtil::makePath($style->getAssetPath());

        $styleEditor->update([
            'imagePath' => FileUtil::getRelativePath(WCF_DIR, $style->getAssetPath()),
        ]);
        $styleEditor = new self(new Style($style->styleID));

        $styleEditor->setVariables($variables, $variablesDarkMode);

        return $style;
    }

    /**
     * @inheritDoc
     */
    public static function resetCache()
    {
        StyleCacheBuilder::getInstance()->reset();
    }
}
