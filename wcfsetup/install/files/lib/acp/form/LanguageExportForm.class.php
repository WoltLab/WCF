<?php

namespace wcf\acp\form;

use wcf\data\language\LanguageEditor;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Shows the language export form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LanguageExportForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.language.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.language.canManageLanguage'];

    /**
     * language id
     * @var int
     */
    public $languageID = 0;

    /**
     * language editor object
     * @var ?LanguageEditor
     */
    public $language;

    /**
     * selected packages
     * @var int
     */
    public $packageID = 0;

    /**
     * true to export custom variables
     * @var bool
     */
    public $exportCustomValues = false;

    /**
     * max package name length
     * @var int
     */
    public $packageNameLength = 0;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->languageID = \intval($_REQUEST['id']);
        }
    }

    #[\Override]
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['packageID'])) {
            $this->packageID = \intval($_POST['packageID']);
        }

        if (isset($_POST['exportCustomValues'])) {
            $this->exportCustomValues = (bool)\intval($_POST['exportCustomValues']);
        }
        if (isset($_POST['languageID'])) {
            $this->languageID = \intval($_POST['languageID']);
        }
    }

    #[\Override]
    public function validate()
    {
        parent::validate();

        $language = LanguageFactory::getInstance()->getLanguage($this->languageID);
        if ($language === null) {
            throw new UserInputException('languageID', 'noValidSelection');
        }
        $this->language = new LanguageEditor($language);

        if (PackageCache::getInstance()->getPackage($this->packageID) === null) {
            throw new UserInputException('packageID');
        }
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        if (empty($_POST) && $this->languageID !== 0) {
            $language = LanguageFactory::getInstance()->getLanguage($this->languageID);
            if ($language === null) {
                throw new IllegalLinkException();
            }
            $this->language = new LanguageEditor($language);
        }
    }

    #[\Override]
    public function save()
    {
        parent::save();

        $package = PackageCache::getInstance()->getPackage($this->packageID);

        \header('Content-Type: text/xml; charset=UTF-8');
        \header(\sprintf(
            'Content-Disposition: attachment; filename="%s_%s.xml"',
            $package->package,
            $this->language->languageCode
        ));
        $this->language->export($this->packageID, $this->exportCustomValues);

        exit;
    }

    #[\Override]
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
            'languageID' => $this->languageID,
            'languages' => LanguageFactory::getInstance()->getLanguages(),
            'packageID' => $this->packageID,
            'packages' => $packages,
            'selectAllPackages' => true,
            'packageNameLength' => $this->packageNameLength,
        ]);
    }
}
