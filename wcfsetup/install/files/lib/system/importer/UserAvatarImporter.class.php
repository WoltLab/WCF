<?php

namespace wcf\system\importer;

use wcf\data\file\File;
use wcf\system\WCF;

/**
 * Imports user avatars.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserAvatarImporter extends AbstractFileImporter
{
    /**
     * @inheritDoc
     */
    protected string $objectType = 'com.woltlab.wcf.user.avatar';

    #[\Override]
    public function import($oldID, array $data, array $additionalData = [])
    {
        // get user id
        $data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
        if (!$data['userID']) {
            return 0;
        }

        $file = $this->importFile($additionalData['fileLocation'], $data['avatarName'] ?? null);
        if ($file === null) {
            return 0;
        }

        $sql = "UPDATE wcf1_user
                SET    avatarFileID = ?
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
