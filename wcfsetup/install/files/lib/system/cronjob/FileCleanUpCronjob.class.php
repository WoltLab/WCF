<?php

namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\data\file\FileEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\WCF;

/**
 * Deletes orphaned files.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
class FileCleanUpCronjob extends AbstractCronjob
{
    #[\Override]
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        $sql = "SELECT  fileID
                FROM    wcf1_file
                WHERE   objectTypeID IS NULL";
        $statement = WCF::getDB()->prepare($sql, 1_000);
        $statement->execute();
        $fileIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if (\MODULE_CONTACT_FORM !== 0 && \CONTACT_FORM_PRUNE_ATTACHMENTS > 0) {
            $fileIDs = \array_merge($fileIDs, $this->getOldContactFileIDs());
        }

        if ($fileIDs === []) {
            return;
        }

        FileEditor::deleteAll($fileIDs);
    }

    /**
     * @return int[]
     */
    private function getOldContactFileIDs(): array
    {
        $sql = "SELECT  fileID
                FROM    wcf1_file
                WHERE   objectTypeID = ?
                    AND uploadTime IS NOT NULL
                    AND uploadTime < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            ObjectTypeCache::getInstance()
                ->getObjectTypeIDByName('com.woltlab.wcf.file', 'com.woltlab.wcf.contact.form'),
            \TIME_NOW - (\CONTACT_FORM_PRUNE_ATTACHMENTS * 86_400),
        ]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}
