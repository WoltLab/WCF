<?php

namespace wcf\data\style;

use ParagonIE\ConstantTime\Hex;
use wcf\command\style\DisableStyle;
use wcf\command\style\EnableStyle;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\user\UserAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\image\ImageHandler;
use wcf\system\request\LinkHandler;
use wcf\system\style\command\CopyStyle;
use wcf\system\style\command\CreateManifest;
use wcf\system\style\StyleHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\ImageUtil;

/**
 * Executes style-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<Style, StyleEditor>
 */
class StyleAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['changeStyle', 'getStyleChooser'];

    /**
     * @inheritDoc
     */
    protected $className = StyleEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.style.canManageStyle'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.style.canManageStyle'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['copy', 'delete', 'markAsTainted', 'update', 'upload'];

    /**
     * style object
     * @var ?Style
     */
    public $style;

    /**
     * style editor object
     * @var ?StyleEditor
     */
    public $styleEditor;

    /**
     * @inheritDoc
     */
    public function create()
    {
        $style = parent::create();

        // add variables
        $this->setStyleVariables($style, false);

        // handle style preview image
        $this->updateStylePreviewImage($style);

        // create favicon data
        $this->updateFavicons($style);

        // handle the cover photo
        $this->updateCoverPhoto($style);

        // handle custom assets
        $this->updateCustomAssets($style);

        return $style;
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        parent::update();

        foreach ($this->getObjects() as $style) {
            // update variables
            $this->setStyleVariables($style->getDecoratedObject(), $this->parameters['isDarkMode'] ?? false);

            // handle style preview image
            $this->updateStylePreviewImage($style->getDecoratedObject());

            // create favicon data
            $this->updateFavicons($style->getDecoratedObject());

            // handle the cover photo
            $this->updateCoverPhoto($style->getDecoratedObject());

            // handle custom assets
            $this->updateCustomAssets($style->getDecoratedObject());

            // reset stylesheet
            StyleHandler::getInstance()->resetStylesheet($style->getDecoratedObject());
        }
    }

    /**
     * @param string $pathComponent
     * @return void
     * @deprecated 5.4 This method is unused.
     */
    protected function removeDirectory($pathComponent)
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
     * Updates style variables for given style.
     */
    private function setStyleVariables(Style $style, bool $isDarkMode): void
    {
        if (!isset($this->parameters['variables']) || !\is_array($this->parameters['variables'])) {
            return;
        }

        $style->loadVariables();
        foreach (['pageLogo', 'pageLogoMobile'] as $type) {
            if (\array_key_exists($type, $this->parameters['uploads'])) {
                /** @var ?\wcf\system\file\upload\UploadFile $file */
                $file = $this->parameters['uploads'][$type];

                if ($style->getVariable($type) && \file_exists($style->getAssetPath() . \basename($style->getVariable($type)))) {
                    if (!$file || $style->getAssetPath() . \basename($style->getVariable($type)) !== $file->getLocation()) {
                        \unlink($style->getAssetPath() . \basename($style->getVariable($type)));
                    }
                }

                if ($file !== null) {
                    if (!$file->isProcessed()) {
                        $fileLocation = $file->getLocation();
                        $extension = \pathinfo($file->getFilename(), \PATHINFO_EXTENSION);
                        $newName = $type . '-' . Hex::encode(\random_bytes(4)) . '.' . $extension;
                        $newLocation = $style->getAssetPath() . $newName;
                        \rename($fileLocation, $newLocation);
                        $this->parameters['variables'][$type] = $newName;
                        $file->setProcessed($newLocation);
                    } else {
                        $this->parameters['variables'][$type] = \basename($file->getLocation());
                    }
                } else {
                    $this->parameters['variables'][$type] = '';
                }
            } else {
                // If the key was not provided then no change is desired. We must re-use
                // the current value, because all variables will be cleared.
                $this->parameters['variables'][$type] = $style->getVariable($type);
            }
        }

        $supportsDarkMode = Style::getVariablesWithDarkModeSupport();

        $sql = "SELECT  variableID, variableName, defaultValue, defaultValueDarkMode
                FROM    wcf1_style_variable";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $variables = $variablesDarkMode = [];
        while ($row = $statement->fetchArray()) {
            $variableName = $row['variableName'];
            $defaultValue = $row['defaultValue'];
            $defaultValueDarkMode = $row['defaultValueDarkMode'];

            if (!isset($this->parameters['variables'][$variableName])) {
                continue;
            }

            $compareAgainst = $defaultValue;
            $isDarkModeVariable = false;
            if ($isDarkMode && \in_array($variableName, $supportsDarkMode, true)) {
                $compareAgainst = $defaultValueDarkMode;
                $isDarkModeVariable = true;
            }

            $value = null;
            if ($this->parameters['variables'][$variableName] != $compareAgainst) {
                $value = $this->parameters['variables'][$variableName];
            }

            if ($isDarkModeVariable) {
                $variablesDarkMode[$row['variableID']] = $value;
            } else {
                $variables[$row['variableID']] = $value;
            }
        }

        $sql = "INSERT INTO             wcf1_style_variable_value
                                        (styleID, variableID, variableValue)
                VALUES                  (?, ?, ?)
                ON DUPLICATE KEY UPDATE variableValue = VALUES(variableValue)";
        $statement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        foreach ($variables as $variableID => $variableValue) {
            $statement->execute([
                $style->styleID,
                $variableID,
                $variableValue,
            ]);
        }
        WCF::getDB()->commitTransaction();

        if ($variablesDarkMode !== []) {
            $sql = "INSERT INTO             wcf1_style_variable_value
                                            (styleID, variableID, variableValueDarkMode)
                    VALUES                  (?, ?, ?)
                    ON DUPLICATE KEY UPDATE variableValueDarkMode = VALUES(variableValueDarkMode)";
            $statement = WCF::getDB()->prepare($sql);

            WCF::getDB()->beginTransaction();
            foreach ($variablesDarkMode as $variableID => $variableValue) {
                $statement->execute([
                    $style->styleID,
                    $variableID,
                    $variableValue,
                ]);
            }
            WCF::getDB()->commitTransaction();
        }
    }

    /**
     * Updates style preview image.
     *
     * @return void
     */
    protected function updateStylePreviewImage(Style $style)
    {
        foreach (['image', 'image2x'] as $type) {
            if (\array_key_exists($type, $this->parameters['uploads'])) {
                /** @var ?\wcf\system\file\upload\UploadFile $file */
                $file = $this->parameters['uploads'][$type];

                if ($style->{$type} && \file_exists($style->getAssetPath() . \basename($style->{$type}))) {
                    if (!$file || $style->getAssetPath() . \basename($style->{$type}) !== $file->getLocation()) {
                        \unlink($style->getAssetPath() . \basename($style->{$type}));
                    }
                }
                if ($file !== null) {
                    $fileLocation = $file->getLocation();
                    if (($imageData = \getimagesize($fileLocation)) === false) {
                        throw new \InvalidArgumentException('The given ' . $type . ' is not an image');
                    }
                    $extension = ImageUtil::getExtensionByMimeType($imageData['mime']);
                    if ($type === 'image') {
                        $newName = 'stylePreview.' . $extension;
                    } elseif ($type === 'image2x') {
                        $newName = 'stylePreview@2x.' . $extension;
                    } else {
                        throw new \LogicException('Unreachable');
                    }
                    $newLocation = $style->getAssetPath() . $newName;
                    \rename($fileLocation, $newLocation);
                    (new StyleEditor($style))->update([
                        $type => FileUtil::getRelativePath(WCF_DIR . 'images/', $style->getAssetPath()) . $newName,
                    ]);

                    $file->setProcessed($newLocation);
                } else {
                    (new StyleEditor($style))->update([
                        $type => '',
                    ]);
                }
            }
        }
    }

    /**
     * Updates style favicon files.
     *
     * @return void
     * @since 3.1
     */
    protected function updateFavicons(Style $style)
    {
        $images = [
            'favicon-48x48.png' => 48,
            'android-chrome-192x192.png' => 192,
            'android-chrome-256x256.png' => 256,
            'android-chrome-512x512.png' => 512,
            'apple-touch-icon.png' => 180,
            'mstile-150x150.png' => 150,
        ];

        if (\array_key_exists('favicon', $this->parameters['uploads'])) {
            /** @var ?\wcf\system\file\upload\UploadFile $file */
            $file = $this->parameters['uploads']['favicon'];

            if ($file !== null) {
                if (!$file->isProcessed()) {
                    $fileLocation = $file->getLocation();
                    if (($imageData = \getimagesize($fileLocation)) === false) {
                        throw new \InvalidArgumentException('The given favicon is not an image');
                    }
                    $extension = ImageUtil::getExtensionByMimeType($imageData['mime']);
                    $newName = "favicon-template." . $extension;
                    $newLocation = $style->getAssetPath() . $newName;
                    \rename($fileLocation, $newLocation);

                    $file->setProcessed($newLocation);

                    // Create browser specific files.
                    $adapter = ImageHandler::getInstance()->getAdapter();
                    $adapter->loadFile($file->getLocation());
                    foreach ($images as $filename => $length) {
                        $thumbnail = $adapter->createThumbnail($length, $length);
                        $adapter->writeImage($thumbnail, $style->getAssetPath() . $filename);
                        // Clear thumbnail as soon as possible to free up the memory.
                        $thumbnail = null;
                    }

                    if (\file_exists($style->getAssetPath() . "favicon.ico")) {
                        \unlink($style->getAssetPath() . "favicon.ico");
                    }

                    (new StyleEditor($style))->update([
                        'hasFavicon' => 1,
                    ]);
                }
            } else {
                foreach ($images as $filename => $length) {
                    if (\file_exists($style->getAssetPath() . $filename)) {
                        \unlink($style->getAssetPath() . $filename);
                    }
                }
                if (\file_exists($style->getAssetPath() . "favicon.ico")) {
                    \unlink($style->getAssetPath() . "favicon.ico");
                }
                foreach (\glob($style->getAssetPath() . "manifest-*.json") as $filename) {
                    \unlink($filename);
                }
                // delete the manifest.json generated before WSC 6.1
                if (\file_exists($style->getAssetPath() . "manifest.json")) {
                    \unlink($style->getAssetPath() . "manifest.json");
                }

                \unlink($style->getAssetPath() . "browserconfig.xml");
                foreach (['png', 'jpg', 'gif'] as $extension) {
                    if (\file_exists($style->getAssetPath() . "favicon-template." . $extension)) {
                        \unlink($style->getAssetPath() . "favicon-template." . $extension);
                    }
                }
                (new StyleEditor($style))->update([
                    'hasFavicon' => 0,
                ]);
            }
        }
        // need to reload the style object to get the updated hasFavicon value
        $style = new Style($style->styleID);
        $command = new CreateManifest($style);
        $command();

        if ($style->hasFavicon) {
            $style->loadVariables();
            $tileColor = $style->getVariable('wcfHeaderBackground', true);

            // update browserconfig.xml
            $browserconfig = <<<BROWSERCONFIG
<?xml version="1.0" encoding="utf-8"?>
<browserconfig>
    <msapplication>
        <tile>
            <square150x150logo src="mstile-150x150.png"/>
            <TileColor>{$tileColor}</TileColor>
        </tile>
    </msapplication>
</browserconfig>
BROWSERCONFIG;
            \file_put_contents($style->getAssetPath() . "browserconfig.xml", $browserconfig);
        }
    }

    /**
     * Updates the style cover photo.
     *
     * @return void
     * @since 3.1
     */
    protected function updateCoverPhoto(Style $style)
    {
        if (\array_key_exists('coverPhoto', $this->parameters['uploads'])) {
            /** @var ?\wcf\system\file\upload\UploadFile $file */
            $file = $this->parameters['uploads']['coverPhoto'];

            if ($style->coverPhotoExtension && \file_exists($style->getCoverPhotoLocation(false))) {
                if (!$file || $style->getCoverPhotoLocation(false) !== $file->getLocation()) {
                    \unlink($style->getCoverPhotoLocation(false));

                    // Remove the WebP variant.
                    if (\file_exists($style->getCoverPhotoLocation(true))) {
                        \unlink($style->getCoverPhotoLocation(true));
                    }
                }
            }
            if ($file !== null) {
                $fileLocation = $file->getLocation();
                if (($imageData = \getimagesize($fileLocation)) === false) {
                    throw new \InvalidArgumentException('The given coverPhoto is not an image');
                }
                $extension = ImageUtil::getExtensionByMimeType($imageData['mime']);
                $outputFilenameWithoutExtension = $style->getAssetPath() . 'coverPhoto';
                $newLocation = "{$outputFilenameWithoutExtension}.{$extension}";
                \rename($fileLocation, $newLocation);

                $result = ImageUtil::createWebpVariant($newLocation, $outputFilenameWithoutExtension);

                $extension = ($result === false) ? 'jpg' : $extension;
                $newLocation = "{$outputFilenameWithoutExtension}.{$extension}";
                (new StyleEditor($style))->update([
                    'coverPhotoExtension' => $extension,
                ]);

                $file->setProcessed($newLocation);
            } else {
                (new StyleEditor($style))->update([
                    'coverPhotoExtension' => '',
                ]);
            }
        }
    }

    /**
     * @return void
     * @since 5.3
     */
    protected function updateCustomAssets(Style $style)
    {
        $customAssetPath = $style->getAssetPath() . 'custom/';

        if (!empty($this->parameters['customAssets']['removed'])) {
            /** @var \wcf\system\file\upload\UploadFile $file */
            foreach ($this->parameters['customAssets']['removed'] as $file) {
                \unlink($file->getLocation());
            }
        }
        if (!empty($this->parameters['customAssets']['added'])) {
            if (!\is_dir($customAssetPath)) {
                FileUtil::makePath($customAssetPath);
            }

            /** @var \wcf\system\file\upload\UploadFile $file */
            foreach ($this->parameters['customAssets']['added'] as $file) {
                \rename($file->getLocation(), $customAssetPath . $file->getFilename());
                $file->setProcessed($customAssetPath . $file->getFilename());
            }
        }
    }

    /**
     * Validates parameters to copy a style.
     *
     * @return void
     * @deprecated 6.2
     */
    public function validateCopy()
    {
        if (!WCF::getSession()->getPermission('admin.style.canManageStyle')) {
            throw new PermissionDeniedException();
        }

        $this->styleEditor = $this->getSingleObject();
    }

    /**
     * Copies a style.
     *
     * @return array{redirectURL: string}
     * @deprecated 6.2 Use `wcf\system\style\command\CopyStyle` instead.
     */
    public function copy()
    {
        $command = new CopyStyle($this->styleEditor->getDecoratedObject());
        $newStyle = $command();

        return [
            'redirectURL' => LinkHandler::getInstance()->getLink('StyleEdit', ['id' => $newStyle->styleID]),
        ];
    }

    /**
     * Validates parameters to change user style.
     *
     * @return void
     */
    public function validateChangeStyle()
    {
        $this->style = $this->getSingleObject()->getDecoratedObject();
        if ($this->style->isDisabled && !WCF::getSession()->getPermission('admin.style.canUseDisabledStyle')) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Changes user style.
     *
     * @return void
     */
    public function changeStyle()
    {
        StyleHandler::getInstance()->changeStyle($this->style->styleID);
        if (StyleHandler::getInstance()->getStyle()->styleID == $this->style->styleID) {
            if (WCF::getUser()->userID) {
                // set this as the permanent style
                $userAction = new UserAction([WCF::getUser()], 'update', [
                    'data' => [
                        'styleID' => $this->style->isDefault ? 0 : $this->style->styleID,
                    ],
                ]);
                $userAction->executeAction();
            } else {
                if ($this->style->isDefault) {
                    WCF::getSession()->unregister('styleID');
                } else {
                    WCF::getSession()->register('styleID', $this->style->styleID);
                }
            }
        }
    }

    /**
     * Validates the 'getStyleChooser' action.
     *
     * @return void
     */
    public function validateGetStyleChooser()
    {
        // does nothing
    }

    /**
     * Returns the style chooser dialog.
     *
     * @return array{actionName: string, template: string}
     */
    public function getStyleChooser()
    {
        $styleList = new StyleList();
        if (!WCF::getSession()->getPermission('admin.style.canUseDisabledStyle')) {
            $styleList->getConditionBuilder()->add("style.isDisabled = ?", [0]);
        }
        $styleList->sqlOrderBy = "style.styleName ASC";
        $styleList->readObjects();

        return [
            'actionName' => 'getStyleChooser',
            'template' => WCF::getTPL()->render('wcf', 'styleChooser', [
                'styleList' => $styleList,
            ]),
        ];
    }

    /**
     * Validates the mark as tainted action.
     *
     * @return void
     * @since 3.0
     */
    public function validateMarkAsTainted()
    {
        if (!WCF::getSession()->getPermission('admin.style.canManageStyle')) {
            throw new PermissionDeniedException();
        }

        $this->styleEditor = $this->getSingleObject();
    }

    /**
     * Marks a style as tainted.
     */
    public function markAsTainted(): void
    {
        // merge definitions
        $variables = $this->styleEditor->getVariables();
        $variables['individualScss'] = \str_replace(
            "/* WCF_STYLE_CUSTOM_USER_MODIFICATIONS */\n",
            '',
            $variables['individualScss']
        );
        $variables['overrideScss'] = \str_replace(
            "/* WCF_STYLE_CUSTOM_USER_MODIFICATIONS */\n",
            '',
            $variables['overrideScss']
        );

        $variablesDarkMode = [];
        $variables = \array_filter($variables, static function ($value, $key) use (&$variablesDarkMode) {
            if (!\str_starts_with($key, Style::DARK_MODE_PREFIX)) {
                return true;
            }

            $variablesDarkMode[\str_replace(Style::DARK_MODE_PREFIX, '', $key)] = $value;

            return false;
        }, \ARRAY_FILTER_USE_BOTH);

        $this->styleEditor->setVariables($variables, $variablesDarkMode);

        $this->styleEditor->update([
            'isTainted' => 1,
            'packageName' => '',
        ]);
    }

    /**
     * @deprecated 6.3
     */
    public function validateToggle()
    {
        $this->validateUpdate();
    }

    /**
     * @deprecated 6.3 use the `EnableStyle` or `DisableStyle` commands instead.
     */
    public function toggle()
    {
        foreach ($this->objects as $editor) {
            if ($editor->isDisabled) {
                (new EnableStyle($editor->getDecoratedObject()))();
            } else {
                (new DisableStyle($editor->getDecoratedObject()))();
            }
        }
    }
}
