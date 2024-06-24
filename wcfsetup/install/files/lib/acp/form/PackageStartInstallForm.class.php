<?php

namespace wcf\acp\form;

use wcf\acp\page\PackageInstallationConfirmPage;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\package\Package;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\package\PackageArchive;
use wcf\system\package\validation\PackageValidationException;
use wcf\system\package\validation\PackageValidationManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;

/**
 * Shows the package install and update form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PackageStartInstallForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.package.install';

    /**
     * updated package object
     * @var Package
     */
    public $package;

    /**
     * data of the uploaded package
     * @var string[]
     */
    public $uploadPackage = '';

    /**
     * archive of the installation/update package
     * @var PackageArchive
     */
    public $archive;

    /**
     * package installation/update queue
     * @var PackageInstallationQueue
     */
    public $queue;

    /**
     * location of the package uploaded via style import
     * @var string
     */
    public $stylePackageImportLocation = '';

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        $this->stylePackageImportLocation = WCF::getSession()->getVar('stylePackageImportLocation');
        if ($this->stylePackageImportLocation) {
            $_POST['t'] = WCF::getSession()->getSecurityToken();
        }
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (!$this->stylePackageImportLocation) {
            if (isset($_FILES['uploadPackage'])) {
                $this->uploadPackage = $_FILES['uploadPackage'];
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        if ($this->stylePackageImportLocation) {
            if (ENABLE_ENTERPRISE_MODE && !WCF::getUser()->hasOwnerAccess()) {
                throw new IllegalLinkException();
            }

            try {
                $this->validateUploadPackage($this->stylePackageImportLocation);
            } catch (UserInputException $e) {
                WCF::getSession()->unregister('stylePackageImportLocation');

                throw $e;
            }
        } elseif (!empty($this->uploadPackage['name'])) {
            if (ENABLE_ENTERPRISE_MODE && !WCF::getUser()->hasOwnerAccess()) {
                throw new IllegalLinkException();
            }

            $this->validateUploadPackage();
        } else {
            throw new UserInputException('uploadPackage');
        }
    }

    /**
     * Validates the upload package input.
     *
     * @param string $filename
     * @throws  UserInputException
     */
    protected function validateUploadPackage($filename = '')
    {
        $this->activeTabMenuItem = 'upload';

        if (empty($filename)) {
            if (empty($this->uploadPackage['tmp_name'])) {
                if (isset($_FILES['uploadPackage']) && $_FILES['uploadPackage']['error'] === \UPLOAD_ERR_INI_SIZE) {
                    throw new UserInputException('uploadPackage', 'exceedsPhpLimit');
                }

                throw new UserInputException('uploadPackage', 'uploadFailed');
            }

            // get filename
            $this->uploadPackage['name'] = FileUtil::getTemporaryFilename(
                'package_',
                \preg_replace('!^.*(?=\.(?:tar\.gz|tgz|tar)$)!i', '', \basename($this->uploadPackage['name']))
            );

            if (!@\move_uploaded_file($this->uploadPackage['tmp_name'], $this->uploadPackage['name'])) {
                throw new UserInputException('uploadPackage', 'uploadFailed');
            }

            $filename = $this->uploadPackage['name'];
        }

        if (!PackageValidationManager::getInstance()->validate($filename, false)) {
            $exception = PackageValidationManager::getInstance()->getException();
            if ($exception instanceof PackageValidationException) {
                WCF::getTPL()->assign([
                    'validationException' => $exception,
                ]);
                throw new UserInputException('uploadPackage', 'noValidPackage');
            }
        }

        $requirements = PackageValidationManager::getInstance()
            ->getPackageValidationArchive()
            ->getArchive()
            ->getOpenRequirements();
        foreach ($requirements as $requirement) {
            if ($requirement['name'] !== 'com.woltlab.wcf') {
                continue;
            }
            if ($requirement['action'] !== 'update') {
                continue;
            }
            if (!isset($requirement['file'])) {
                continue;
            }

            $existingVersion = \explode('.', $requirement['existingVersion']);
            $minversion = \explode('.', $requirement['minversion']);
            if (
                $existingVersion[0] !== $minversion[0]
                || $existingVersion[1] !== $minversion[1]
            ) {
                throw new UserInputException('uploadPackage', 'majorUpgrade');
            }
        }

        $this->package = PackageValidationManager::getInstance()->getPackageValidationArchive()->getPackage();
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        // get new process no
        $processNo = PackageInstallationQueue::getNewProcessNo();

        // obey foreign key
        $packageID = $this->package ? $this->package->packageID : null;

        $archive = null;
        if ($this->stylePackageImportLocation) {
            $archive = $this->stylePackageImportLocation;
        } elseif (!empty($this->uploadPackage['tmp_name'])) {
            $archive = $this->uploadPackage['name'];
        }

        // insert queue
        $isApplication = PackageValidationManager::getInstance()->getPackageValidationArchive()->getArchive()->getPackageInfo('isApplication');
        $this->queue = PackageInstallationQueueEditor::create([
            'processNo' => $processNo,
            'userID' => WCF::getUser()->userID,
            'package' => PackageValidationManager::getInstance()->getPackageValidationArchive()->getArchive()->getPackageInfo('name'),
            'packageName' => PackageValidationManager::getInstance()->getPackageValidationArchive()->getArchive()->getLocalizedPackageInfo('packageName'),
            'packageID' => $packageID,
            'archive' => $archive,
            'action' => $this->package != null ? 'update' : 'install',
            'isApplication' => !$isApplication ? '0' : '1',
        ]);

        $this->saved();

        HeaderUtil::redirect(LinkHandler::getInstance()->getControllerLink(
            PackageInstallationConfirmPage::class,
            [
                'queueID' => $this->queue->queueID,
            ]
        ));

        exit;
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        $majorMinorVersion = \preg_replace('/^(\d+\.\d+)\..*$/', '\\1', \WCF_VERSION);

        WCF::getTPL()->assign([
            'package' => $this->package,
            'installingImportedStyle' => $this->stylePackageImportLocation != '',
            'majorMinorVersion' => $majorMinorVersion,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        if (!WCF::getSession()->getPermission('admin.configuration.package.canInstallPackage') && !WCF::getSession()->getPermission('admin.configuration.package.canUpdatePackage')) {
            throw new PermissionDeniedException();
        }

        parent::show();
    }
}
