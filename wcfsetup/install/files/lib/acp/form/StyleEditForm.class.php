<?php

namespace wcf\acp\form;

use wcf\data\style\Style;
use wcf\data\style\StyleAction;
use wcf\data\style\StyleEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\file\upload\UploadFile;
use wcf\system\file\upload\UploadHandler;
use wcf\system\language\I18nHandler;
use wcf\system\style\StyleCompiler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Shows the style edit form.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class StyleEditForm extends StyleAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.style.list';

    /**
     * style object
     * @var Style
     */
    public $style;

    /**
     * style id
     * @var int
     */
    public $styleID = 0;

    public bool $isDarkMode = false;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        if (isset($_REQUEST['id'])) {
            $this->styleID = \intval($_REQUEST['id']);
        }
        $this->style = new Style($this->styleID);
        if (!$this->style->styleID) {
            throw new IllegalLinkException();
        }

        if ($this->style->hasDarkMode) {
            $this->isDarkMode = ($_REQUEST['isDarkMode'] ?? '') === '1';
        }

        parent::readParameters();
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        if (!$this->style->isTainted) {
            $this->parseOverrides('overrideScssCustom');
        }
    }

    /**
     * @inheritDoc
     */
    public function validateIndividualScss()
    {
        // If the style has a dark mode then `$variables` contains either the
        // values for the “light” theme or the dark variant. It is necessary to
        // inject the complementary values, otherwise the compiled style is
        // incomplete and causes issue when the file is being promoted to be
        // the actual stylesheet when saving.
        if ($this->style->hasDarkMode) {
            $variables = $this->style->getVariables();
            $supportsDarkMode = Style::getVariablesWithDarkModeSupport();
            if ($this->isDarkMode) {
                foreach ($this->variables as $key => $value) {
                    if (\in_array($key, $supportsDarkMode, true)) {
                        $key = Style::DARK_MODE_PREFIX . $key;
                    }

                    $variables[$key] = $value;
                }
            } else {
                foreach ($this->variables as $key => $value) {
                    $variables[$key] = $value;
                }
            }
        } else {
            $variables = $this->variables;
        }

        if (!$this->style->isTainted) {
            $variables['individualScss'] = Style::joinLessVariables(
                $variables['individualScss'],
                $variables['individualScssCustom']
            );
            if ($this->style->hasDarkMode) {
                $variables['individualScssDarkMode'] = Style::joinLessVariables(
                    $variables['individualScssDarkMode'],
                    $variables['individualScssDarkModeCustom']
                );

                unset($variables['individualScssDarkModeCustom']);
            }
            $variables['overrideScss'] = Style::joinLessVariables(
                $variables['overrideScss'],
                $variables['overrideScssCustom']
            );

            unset($variables['individualScssCustom']);
            unset($variables['overrideScssCustom']);
        }

        $variables = \array_merge(StyleCompiler::getDefaultVariables(), $variables);

        $this->styleTestFileDir = FileUtil::getTemporaryFilename('style_');
        FileUtil::makePath($this->styleTestFileDir);

        $result = StyleCompiler::getInstance()->testStyle(
            $this->styleTestFileDir,
            $this->styleName,
            $this->style->imagePath,
            $variables,
            null,
        );

        if ($result !== null) {
            \rmdir($this->styleTestFileDir);

            throw new UserInputException('individualScss', [
                'message' => $result->getMessage(),
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function enforcePackageNameRestriction()
    {
        if ($this->style->isTainted) {
            parent::enforcePackageNameRestriction();
        }
    }

    /**
     * @inheritDoc
     */
    protected function readStyleVariables()
    {
        $this->variables = $this->style->getVariables();

        $prefixLength = \strlen(Style::DARK_MODE_PREFIX);
        foreach ($this->variables as $key => $value) {
            if (\str_starts_with($key, Style::DARK_MODE_PREFIX)) {
                unset($this->variables[$key]);

                if ($this->isDarkMode && \str_starts_with($value, 'rgba(')) {
                    $this->variables[\substr($key, $prefixLength)] = $value;
                }
            }
        }

        // fix empty values ~""
        foreach ($this->variables as &$variableValue) {
            if ($variableValue == '~""') {
                $variableValue = '';
            }
        }
        unset($variableValue);

        if (!$this->style->isTainted) {
            $tmp = Style::splitLessVariables($this->variables['individualScss']);
            $this->variables['individualScss'] = $tmp['preset'];
            $this->variables['individualScssCustom'] = $tmp['custom'];

            $tmp = Style::splitLessVariables($this->variables['individualScssDarkMode']);
            $this->variables['individualScssDarkMode'] = $tmp['preset'];
            $this->variables['individualScssDarkModeCustom'] = $tmp['custom'];

            $tmp = Style::splitLessVariables($this->variables['overrideScss']);
            $this->variables['overrideScss'] = $tmp['preset'];
            $this->variables['overrideScssCustom'] = $tmp['custom'];
        }

        if ($this->variables['pageLogo'] && \file_exists($this->style->getAssetPath() . $this->variables['pageLogo'])) {
            $file = new UploadFile(
                $this->style->getAssetPath() . $this->variables['pageLogo'],
                \basename($this->variables['pageLogo']),
                true,
                true,
                true
            );
            UploadHandler::getInstance()->registerFilesByField('pageLogo', [
                $file,
            ]);
        }
        if ($this->variables['pageLogoMobile'] && \file_exists($this->style->getAssetPath() . $this->variables['pageLogoMobile'])) {
            $file = new UploadFile(
                $this->style->getAssetPath() . $this->variables['pageLogoMobile'],
                \basename($this->variables['pageLogoMobile']),
                true,
                true,
                true
            );
            UploadHandler::getInstance()->registerFilesByField('pageLogoMobile', [
                $file,
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function setVariables()
    {
        parent::setVariables();

        if (!$this->style->isTainted) {
            $this->specialVariables[] = 'individualScssCustom';
            $this->specialVariables[] = 'individualScssDarkModeCustom';
            $this->specialVariables[] = 'overrideScssCustom';
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        I18nHandler::getInstance()->setOptions(
            'styleDescription',
            PACKAGE_ID,
            $this->style->styleDescription,
            'wcf.style.styleDescription\d+'
        );

        if (empty($_POST)) {
            $this->authorName = $this->style->authorName;
            $this->authorURL = $this->style->authorURL;
            $this->copyright = $this->style->copyright;
            $this->isTainted = $this->style->isTainted;
            $this->license = $this->style->license;
            $this->packageName = $this->style->packageName;
            $this->styleDate = $this->style->styleDate;
            $this->styleDescription = $this->style->styleDescription;
            $this->styleName = $this->style->styleName;
            $this->styleVersion = $this->style->styleVersion;
            $this->templateGroupID = $this->style->templateGroupID;
            if ($this->style->image && \file_exists(WCF_DIR . 'images/' . $this->style->image)) {
                $file = new UploadFile(
                    WCF_DIR . 'images/' . $this->style->image,
                    $this->style->image,
                    true,
                    true,
                    false
                );
                UploadHandler::getInstance()->registerFilesByField('image', [
                    $file,
                ]);
            }
            if ($this->style->image2x && \file_exists(WCF_DIR . 'images/' . $this->style->image2x)) {
                $file = new UploadFile(
                    WCF_DIR . 'images/' . $this->style->image2x,
                    $this->style->image2x,
                    true,
                    true,
                    false
                );
                UploadHandler::getInstance()->registerFilesByField('image2x', [
                    $file,
                ]);
            }
            if ($this->style->coverPhotoExtension && \file_exists($this->style->getCoverPhotoLocation(false))) {
                $file = new UploadFile(
                    $this->style->getCoverPhotoLocation(false),
                    $this->style->getCoverPhoto(false),
                    true,
                    true,
                    false
                );
                UploadHandler::getInstance()->registerFilesByField('coverPhoto', [
                    $file,
                ]);
            }
            if ($this->style->hasFavicon) {
                foreach (['png', 'jpg', 'gif'] as $extension) {
                    $filename = "favicon-template." . $extension;
                    if (\file_exists($this->style->getAssetPath() . $filename)) {
                        $file = new UploadFile(
                            $this->style->getAssetPath() . $filename,
                            $filename,
                            true,
                            true,
                            false
                        );
                        UploadHandler::getInstance()->registerFilesByField('favicon', [
                            $file,
                        ]);
                        break;
                    }
                }
            }

            UploadHandler::getInstance()->registerFilesByField('customAssets', \array_map(static function ($filename) {
                return new UploadFile($filename, \basename($filename), true, true, true);
            }, \glob($this->style->getAssetPath() . 'custom/*')));
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        if (!$this->style->isTainted) {
            $this->variables['individualScss'] = Style::joinLessVariables(
                $this->variables['individualScss'],
                $this->variables['individualScssCustom']
            );
            if ($this->style->hasDarkMode) {
                $this->variables['individualScssDarkMode'] = Style::joinLessVariables(
                    $this->variables['individualScssDarkMode'],
                    $this->variables['individualScssDarkModeCustom']
                );
            }
            $this->variables['overrideScss'] = Style::joinLessVariables(
                $this->variables['overrideScss'],
                $this->variables['overrideScssCustom']
            );

            unset($this->variables['individualScssCustom']);
            unset($this->variables['individualScssDarkModeCustom']);
            unset($this->variables['overrideScssCustom']);
        }

        // Remove control characters that break the SCSS parser, see https://stackoverflow.com/a/23066553
        $this->variables['individualScss'] = \preg_replace('/[^\PC\s]/u', '', $this->variables['individualScss']);
        if ($this->style->hasDarkMode) {
            $this->variables['individualScssDarkMode'] = \preg_replace('/[^\PC\s]/u', '', $this->variables['individualScssDarkMode']);
        }

        $this->objectAction = new StyleAction([$this->style], 'update', [
            'data' => \array_merge($this->additionalFields, [
                'styleName' => $this->styleName,
                'templateGroupID' => $this->templateGroupID,
                'styleVersion' => $this->styleVersion,
                'styleDate' => $this->styleDate,
                'copyright' => $this->copyright,
                'packageName' => $this->packageName,
                'license' => $this->license,
                'authorName' => $this->authorName,
                'authorURL' => $this->authorURL,
            ]),
            'uploads' => $this->uploads,
            'customAssets' => $this->customAssets,
            'tmpHash' => $this->tmpHash,
            'variables' => $this->variables,
            'isDarkMode' => $this->isDarkMode,
        ]);
        $this->objectAction->executeAction();

        // save compiled style
        if (
            $this->styleTestFileDir
            && \file_exists($this->styleTestFileDir . '/style.css')
            && \file_exists($this->styleTestFileDir . '/style-rtl.css')
        ) {
            $styleFilename = StyleCompiler::getFilenameForStyle($this->style);
            \rename($this->styleTestFileDir . '/style.css', $styleFilename . '.css');
            \rename($this->styleTestFileDir . '/style-rtl.css', $styleFilename . '-rtl.css');
            if (\file_exists($this->styleTestFileDir . '/style-preload.json')) {
                \rename($this->styleTestFileDir . '/style-preload.json', $styleFilename . '-preload.json');
            }

            \rmdir($this->styleTestFileDir);
        }

        // save description
        I18nHandler::getInstance()->save(
            'styleDescription',
            'wcf.style.styleDescription' . $this->style->styleID,
            'wcf.style'
        );

        $styleEditor = new StyleEditor($this->style);
        $styleEditor->update([
            'styleDescription' => 'wcf.style.styleDescription' . $this->style->styleID,
        ]);

        // call saved event
        $this->saved();

        // reload style object to update preview image
        $this->style = new Style($this->style->styleID);

        if (!$this->style->isTainted) {
            $tmp = Style::splitLessVariables($this->variables['individualScss']);
            $this->variables['individualScss'] = $tmp['preset'];
            $this->variables['individualScssCustom'] = $tmp['custom'];

            if ($this->style->hasDarkMode) {
                $tmp = Style::splitLessVariables($this->variables['individualScssDarkMode']);
                $this->variables['individualScssDarkMode'] = $tmp['preset'];
                $this->variables['individualScssDarkModeCustom'] = $tmp['custom'];
            }

            $tmp = Style::splitLessVariables($this->variables['overrideScss']);
            $this->variables['overrideScss'] = $tmp['preset'];
            $this->variables['overrideScssCustom'] = $tmp['custom'];
        }

        WCF::getTPL()->assign('success', true);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        I18nHandler::getInstance()->assignVariables(!empty($_POST));

        WCF::getTPL()->assign([
            'action' => 'edit',
            'isTainted' => $this->style->isTainted,
            'style' => $this->style,
            'styleID' => $this->styleID,
            'isDarkMode' => $this->isDarkMode,
        ]);
    }
}
