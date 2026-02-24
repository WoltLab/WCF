<?php

namespace wcf\system\worker;

use wcf\data\file\FileEditor;
use wcf\data\unfurl\url\UnfurlUrl;
use wcf\data\unfurl\url\UnfurlUrlEditor;
use wcf\data\unfurl\url\UnfurlUrlList;
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

        $sql = "DELETE FROM wcf1_unfurl_url_image
                WHERE       imageID = ?";
        $deleteStatement = WCF::getDB()->prepare($sql);

        $deleteFileIDs = [];

        foreach ($this->getObjectList()->getObjects() as $unfurlUrl) {
            if (!$unfurlUrl->isStored || $unfurlUrl->imageID === null) {
                continue;
            }

            if (!URL_UNFURLING_SAVE_IMAGES) {
                // delete stored images
                if ($unfurlUrl->fileID !== null) {
                    $deleteFileIDs[] = $unfurlUrl->fileID;
                } else {
                    $fileLocation = $this->getOldFileLocation($unfurlUrl);
                    @\unlink($fileLocation);
                }

                $deleteStatement->execute([$unfurlUrl->imageID]);
            } elseif ($unfurlUrl->fileID === null) {
                $fileLocation = $this->getOldFileLocation($unfurlUrl);

                $file = UnfurlUrlEditor::saveUnfurlImage(
                    $fileLocation,
                    \pathinfo($unfurlUrl->imageUrl, PATHINFO_FILENAME)
                );

                @\unlink($fileLocation);

                $updateStatement->execute([
                    $file !== null ? 1 : 0,
                    $file?->fileID,
                    $unfurlUrl->imageID,
                ]);
            }
        }

        if ($deleteFileIDs !== []) {
            FileEditor::deleteAll($deleteFileIDs);
        }
    }

    private function getOldFileLocation(UnfurlUrl $unfurlUrl): string
    {
        return \sprintf(
            '%s%s%s/%s.%s',
            WCF_DIR,
            UnfurlUrl::IMAGE_DIR,
            \substr($unfurlUrl->imageUrlHash, 0, 2),
            $unfurlUrl->imageUrlHash,
            $unfurlUrl->imageExtension
        );
    }
}
