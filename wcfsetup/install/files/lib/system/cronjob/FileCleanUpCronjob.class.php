<?php

namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\data\file\FileBuilder;
use wcf\data\file\temporary\FileTemporaryBuilder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Deletes orphaned files and stale uploads.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
class FileCleanUpCronjob extends AbstractCronjob
{
    /**
     * Uploads that have been started more than 24 hours ago are considered to
     * be abandoned. Large files on slow connections can legitimately take
     * hours to upload, but not a full day.
     */
    private const TEMPORARY_FILE_LIFETIME = 86_400;

    #[\Override]
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        $this->pruneTemporaryFiles();
        $this->pruneOrphanedChunks();

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

        FileBuilder::deleteAll($fileIDs);
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

    private function pruneTemporaryFiles(): void
    {
        $sql = "SELECT  identifier
                FROM    wcf1_file_temporary
                WHERE   time < ?";
        $statement = WCF::getDB()->prepare($sql, 1_000);
        $statement->execute([
            \TIME_NOW - self::TEMPORARY_FILE_LIFETIME,
        ]);
        $identifiers = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if ($identifiers !== []) {
            FileTemporaryBuilder::deleteAll($identifiers);
        }
    }

    /**
     * Deletes chunks that no longer belong to an upload. These are left behind
     * when an upload is discarded while a request writing a chunk is still in
     * progress.
     */
    private function pruneOrphanedChunks(): void
    {
        $directory = \WCF_DIR . '_data/private/fileUpload/';
        if (!\is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        $pathnames = [];
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'bin') {
                continue;
            }

            // The file disappears if the upload is completed in the meantime.
            $mtime = @\filemtime($file->getPathname());
            if ($mtime !== false && $mtime < \TIME_NOW - self::TEMPORARY_FILE_LIFETIME) {
                $pathnames[$file->getBasename('.bin')] = $file->getPathname();
            }
        }

        foreach (\array_chunk($pathnames, 1_000, true) as $batch) {
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add('identifier IN (?)', [\array_keys($batch)]);

            $sql = "SELECT  identifier
                    FROM    wcf1_file_temporary
                    {$conditions}";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditions->getParameters());
            $existingIdentifiers = $statement->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($batch as $identifier => $pathname) {
                if (!\in_array($identifier, $existingIdentifiers, true)) {
                    @\unlink($pathname);
                }
            }
        }
    }
}
