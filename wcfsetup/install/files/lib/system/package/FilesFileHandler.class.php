<?php

namespace wcf\system\package;

use wcf\data\application\Application;
use wcf\data\package\Package;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * File handler implementation for the installation of regular files.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Package
 */
class FilesFileHandler extends PackageInstallationFileHandler
{
    /**
     * @inheritDoc
     */
    public function checkFiles(array $files)
    {
        if ($this->packageInstallation->getPackage()->package != 'com.woltlab.wcf') {
            if (!empty($files)) {
                // get registered files of other packages for the
                // same application
                $conditions = new PreparedStatementConditionBuilder();
                $conditions->add('packageID <> ?', [$this->packageInstallation->getPackageID()]);
                $conditions->add('filename IN (?)', [$files]);
                $conditions->add('application = ?', [$this->application]);

                $sql = "SELECT  filename, packageID
                        FROM    wcf1_package_installation_file_log
                        {$conditions}";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditions->getParameters());
                $lockedFiles = $statement->fetchMap('filename', 'packageID');

                // check delivered files
                if (!empty($lockedFiles)) {
                    foreach ($files as $file) {
                        if (isset($lockedFiles[$file])) {
                            $owningPackage = new Package($lockedFiles[$file]);

                            throw new SystemException("A package can't overwrite files from other packages. Only an update from the package which owns the file can do that. (Package '" . $this->packageInstallation->getPackage()->package . "' tries to overwrite file '" . $file . "', which is owned by package '" . $owningPackage->package . "')");
                        }
                    }
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function logFiles(array $files)
    {
        if (empty($files)) {
            return;
        }

        $baseDirectory = Application::getDirectory($this->application);

        $sql = "INSERT INTO             wcf1_package_installation_file_log
                                        (packageID, filename, application, sha256, lastUpdated)
                VALUES                  (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE sha256 = VALUES(sha256),
                                        lastUpdated = VALUES(lastUpdated)";
        $statement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        foreach ($files as $file) {
            $statement->execute([
                $this->packageInstallation->getPackageID(),
                $file,
                $this->application,
                \hash_file('sha256', $baseDirectory . $file, true),
                TIME_NOW,
            ]);
        }
        WCF::getDB()->commitTransaction();
    }
}
