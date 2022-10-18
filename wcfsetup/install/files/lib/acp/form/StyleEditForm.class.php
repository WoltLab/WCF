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
 * @package WoltLabSuite\Core\Acp\Form
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

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        if (isset($_REQUEST['id'])) {
            $this->styleID = (int)$_REQUEST['id'];
        }
        $this->style = new Style($this->styleID);
        if (!$this->style->styleID) {
            throw new IllegalLinkException();
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
        $variables = $this->variables;
        if (!$this->style->isTainted) {
            $variables['individualScss'] = Style::joinLessVariables(
                $variables['individualScss'],
                $variables['individualScssCustom']
            );
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
            $this->apiVersion,
            $this->style->imagePath,
            $variables
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
    protected function validateApiVersion()
    {
        if ($this->style->isTainted) {
            parent::validateApiVersion();
        } else {
            $this->apiVersion = $this->style->apiVersion;
        }
    }

    /**
     * @inheritDoc
     */
    protected function readStyleVariables()
    {
        $this->variables = $this->style->getVariables();

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
            $this->apiVersion = $this->style->apiVersion;
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
            $this->variables['overrideScss'] = Style::joinLessVariables(
                $this->variables['overrideScss'],
                $this->variables['overrideScssCustom']
            );

            unset($this->variables['individualScssCustom']);
            unset($this->variables['overrideScssCustom']);
        }

        // Remove control characters that break the SCSS parser, see https://stackoverflow.com/a/23066553
        $this->variables['individualScss'] = \preg_replace('/[^\PC\s]/u', '', $this->variables['individualScss']);

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
                'apiVersion' => $this->apiVersion,
            ]),
            'uploads' => $this->uploads,
            'customAssets' => $this->customAssets,
            'tmpHash' => $this->tmpHash,
            'variables' => $this->variables,
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
        ]);
    }
}
