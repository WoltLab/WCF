<?php

namespace wcf\acp\form;

use wcf\data\language\Language;
use wcf\data\language\LanguageEditor;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\event\language\LanguageImported;
use wcf\form\AbstractForm;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\XML;

/**
 * Shows the language import form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LanguageImportForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.language.import';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.language.canManageLanguage'];

    /**
     * file name
     * @var string
     */
    public $filename = '';

    /**
     * language object
     * @var Language
     */
    public $language;

    /**
     * list of available languages
     * @var Language[]
     */
    public $languages = [];

    /**
     * source language object
     * @var Language
     */
    public $sourceLanguage;

    /**
     * source language id
     * @var int
     */
    public $sourceLanguageID = 0;

    /**
     * @var int
     * @since   5.4
     */
    public $packageID = 0;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        $this->languages = LanguageFactory::getInstance()->getLanguages();
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_FILES['languageUpload']) && !empty($_FILES['languageUpload']['tmp_name'])) {
            $this->filename = $_FILES['languageUpload']['tmp_name'];
        }
        if (isset($_POST['sourceLanguageID'])) {
            $this->sourceLanguageID = \intval($_POST['sourceLanguageID']);
        }
        if (isset($_POST['packageID'])) {
            $this->packageID = \intval($_POST['packageID']);
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        // check file
        if (!\file_exists($this->filename)) {
            throw new UserInputException('languageUpload');
        }

        if (empty($this->sourceLanguageID)) {
            throw new UserInputException('sourceLanguageID');
        }

        // get language
        $this->sourceLanguage = LanguageFactory::getInstance()->getLanguage($this->sourceLanguageID);
        if (!$this->sourceLanguage->languageID) {
            throw new UserInputException('sourceLanguageID');
        }

        if (!PackageCache::getInstance()->getPackage($this->packageID)) {
            throw new UserInputException('packageID');
        }

        // try to import
        try {
            // open xml document
            $xml = new XML();
            $xml->load($this->filename);

            // import xml document
            $this->language = LanguageEditor::importFromXML($xml, $this->packageID, $this->sourceLanguage);

            // copy content
            if (!isset($this->languages[$this->language->languageID])) {
                LanguageEditor::copyLanguageContent($this->sourceLanguage->languageID, $this->language->languageID);
            }
        } catch (SystemException $e) {
            throw new UserInputException('languageUpload', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            throw new UserInputException('languageUpload', $e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        LanguageFactory::getInstance()->clearCache();
        LanguageFactory::getInstance()->deleteLanguageCache();

        EventHandler::getInstance()->fire(new LanguageImported(new Language($this->language->languageID)));

        $this->saved();

        // reset fields
        $this->sourceLanguageID = 0;
        $this->packageID = 0;

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        $packages = PackageCache::getInstance()->getPackages();
        $collator = new \Collator(WCF::getLanguage()->getLocale());
        \usort(
            $packages,
            static fn (Package $a, Package $b) => $collator->compare($a->getName(), $b->getName())
        );

        WCF::getTPL()->assign([
            'languages' => $this->languages,
            'sourceLanguageID' => $this->sourceLanguageID,
            'packages' => $packages,
            'packageID' => $this->packageID,
        ]);
    }
}
