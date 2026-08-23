<?php

namespace wcf\system\worker;

use wcf\data\file\FileEditor;
use wcf\data\unfurl\url\UnfurlUrl;
use wcf\data\unfurl\url\UnfurlUrlEditor;
use wcf\data\unfurl\url\UnfurlUrlList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Worker implementation for unfurl url rebuild data.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractLinearRebuildDataWorker<UnfurlUrlList>
 */
final class UnfurlUrlRebuildDataWorker extends AbstractLinearRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = UnfurlUrlList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 10;

    #[\Override]
    public function execute()
    {
        parent::execute();

        if (\count($this->getObjectList()) === 0) {
            return;
        }

        $sql = "UPDATE wcf1_unfurl_url_image
                SET    isStored = ?,
                       fileID = ?
                WHERE  imageID = ?";
        $updateStatement = WCF::getDB()->prepare($sql);

        $deleteFileIDs = [];
        $cleanUpImageIDs = [];
        foreach ($this->getObjectList()->getObjects() as $unfurlUrl) {
            if ($unfurlUrl->imageID === null || $unfurlUrl->getImage()->isStored === 0) {
                continue;
            }

            if (\URL_UNFURLING_SAVE_IMAGES === 0) {
                // delete stored images
                if ($unfurlUrl->getImage()->fileID !== null) {
                    $deleteFileIDs[] = $unfurlUrl->getImage()->fileID;
                } else {
                    $fileLocation = $this->getOldFileLocation($unfurlUrl);
                    @\unlink($fileLocation);
                }

                $cleanUpImageIDs[] = $unfurlUrl->imageID;
            } elseif ($unfurlUrl->getImage()->fileID === null) {
                $fileLocation = $this->getOldFileLocation($unfurlUrl);

                $file = UnfurlUrlEditor::saveUnfurlImage(
                    $fileLocation,
                    \pathinfo($unfurlUrl->getImage()->imageUrl, \PATHINFO_FILENAME)
                );

                @\unlink($fileLocation);

                $updateStatement->execute([
                    $file !== null ? 1 : 0,
                    $file?->fileID,
                    $unfurlUrl->imageID,
                ]);
            }
        }

        if ($cleanUpImageIDs !== []) {
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("imageID IN (?)", [$cleanUpImageIDs]);
            $sql = "DELETE FROM     wcf1_unfurl_url_image
                    " . $conditions;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditions->getParameters());
        }

        if ($deleteFileIDs !== []) {
            FileEditor::deleteAll($deleteFileIDs);
        }
    }

    private function getOldFileLocation(UnfurlUrl $unfurlUrl): string
    {
        return \sprintf(
            '%s%s%s/%s.%s',
            \WCF_DIR,
            UnfurlUrl::IMAGE_DIR,
            \substr($unfurlUrl->getImage()->imageUrlHash, 0, 2),
            $unfurlUrl->getImage()->imageUrlHash,
            $unfurlUrl->getImage()->imageExtension
        );
    }
}
