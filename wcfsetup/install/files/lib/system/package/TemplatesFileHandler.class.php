<?php

namespace wcf\system\package;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * File handler implementation for the installation of template files.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TemplatesFileHandler extends ACPTemplatesFileHandler
{
    /**
     * @inheritDoc
     */
    protected $supportsTemplateGroups = true;

    /**
     * @inheritDoc
     */
    protected $tableName = 'template';

    /**
     * @inheritDoc
     */
    public function logFiles(array $files)
    {
        $packageID = $this->packageInstallation->getPackageID();

        // remove file extension
        foreach ($files as &$file) {
            $file = \substr($file, 0, -4);
        }
        unset($file);

        // get existing templates
        $updateTemplateIDs = [];
        $sql = "SELECT  templateName, templateID
                FROM    wcf1_template
                WHERE   packageID = ?
                    AND application = ?
                    AND templateGroupID IS NULL";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$packageID, $this->application]);
        $existingTemplates = $statement->fetchMap('templateName', 'templateID');

        // save new templates
        $sql = "INSERT INTO wcf1_template
                            (packageID, templateName, lastModificationTime, application)
                VALUES      (?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($files as $file) {
            if (isset($existingTemplates[$file])) {
                $updateTemplateIDs[] = $existingTemplates[$file];
                continue;
            }

            $statement->execute([
                $packageID,
                $file,
                TIME_NOW,
                $this->application,
            ]);
        }

        if (!empty($updateTemplateIDs)) {
            // update old templates
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('templateID IN (?)', [$updateTemplateIDs]);

            $sql = "UPDATE  wcf1_template
                    SET     lastModificationTime = ?
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute(\array_merge([TIME_NOW], $conditionBuilder->getParameters()));
        }
    }
}
