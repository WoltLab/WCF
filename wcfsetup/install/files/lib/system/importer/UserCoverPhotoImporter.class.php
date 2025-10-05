<?php

namespace wcf\system\importer;

use wcf\data\file\File;
use wcf\system\WCF;

/**
 * Imports user profile cover photos.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class UserCoverPhotoImporter extends AbstractFileImporter
{
    /**
     * @inheritDoc
     */
    protected string $objectType = 'com.woltlab.wcf.user.coverPhoto';

    #[\Override]
    public function import($oldID, array $data, array $additionalData = [])
    {
        $data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
        if (!$data['userID']) {
            return 0;
        }

        $file = $this->importFile($additionalData['fileLocation'], $data['coverPhotoName'] ?? null);
        if ($file === null) {
            return 0;
        }

        $sql = "UPDATE wcf1_user
                SET    coverPhotoFileID = ?
                WHERE  userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $file->fileID,
            $data['userID']
        ]);

        return $file->fileID;
    }

    protected function isValidFile(File $file): bool
    {
        return $file->isImage();
    }
}
