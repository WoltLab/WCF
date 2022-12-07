<?php

namespace wcf\system\setup;

use wcf\system\WCF;

/**
 * Special file handler used during setup to log the deployed files.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Setup
 */
class SetupFileHandler implements IFileHandler
{
    /**
     * @inheritDoc
     */
    public function checkFiles(array $files)
    {
        /* does nothing */
    }

    /**
     * @inheritDoc
     */
    public function logFiles(array $files)
    {
        $acpTemplateInserts = $fileInserts = [];
        foreach ($files as $file) {
            $match = [];
            if (\preg_match('~^acp/templates/([^/]+)\.tpl$~', $file, $match)) {
                // acp template
                $acpTemplateInserts[] = $match[1];
            } else {
                // regular file
                $fileInserts[] = $file;
            }
        }

        $sql = "INSERT INTO wcf1_acp_template
                            (packageID, templateName, application)
                VALUES      (?, ?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);

        WCF::getDB()->beginTransaction();
        foreach ($acpTemplateInserts as $acpTemplate) {
            $statement->execute([1, $acpTemplate, 'wcf']);
        }
        WCF::getDB()->commitTransaction();

        $sql = "INSERT INTO wcf1_package_installation_file_log
                            (packageID, filename, application, sha256, lastUpdated)
                VALUES      (?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);

        WCF::getDB()->beginTransaction();
        foreach ($fileInserts as $file) {
            $statement->execute([
                1,
                $file,
                'wcf',
                \hash_file('sha256', \WCF_DIR . $file, true),
                \TIME_NOW,
            ]);
        }
        WCF::getDB()->commitTransaction();
    }
}
