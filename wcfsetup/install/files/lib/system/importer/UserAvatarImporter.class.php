<?php

namespace wcf\system\importer;

use wcf\data\user\avatar\UserAvatar;
use wcf\data\user\avatar\UserAvatarEditor;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\ImageUtil;

/**
 * Imports user avatars.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserAvatarImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = UserAvatar::class;

    /**
     * @inheritDoc
     */
    public function import($oldID, array $data, array $additionalData = [])
    {
        // check file location
        if (!\is_readable($additionalData['fileLocation'])) {
            return 0;
        }

        // get image size
        $imageData = @\getimagesize($additionalData['fileLocation']);
        if ($imageData === false) {
            return 0;
        }
        $data['width'] = $imageData[0];
        $data['height'] = $imageData[1];
        $data['avatarExtension'] = ImageUtil::getExtensionByMimeType($imageData['mime']);
        $data['fileHash'] = \sha1_file($additionalData['fileLocation']);

        // check image type
        if ($imageData[2] != \IMAGETYPE_GIF && $imageData[2] != \IMAGETYPE_JPEG && $imageData[2] != \IMAGETYPE_PNG) {
            return 0;
        }

        // get user id
        $data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
        if (!$data['userID']) {
            return 0;
        }

        // save avatar
        $avatar = UserAvatarEditor::create($data);

        // check avatar directory
        // and create subdirectory if necessary
        $dir = \dirname($avatar->getLocation());
        if (!@\file_exists($dir)) {
            FileUtil::makePath($dir);
        }

        // copy file
        try {
            if (!\copy($additionalData['fileLocation'], $avatar->getLocation())) {
                throw new SystemException();
            }

            // update owner
            $sql = "UPDATE  wcf1_user
                    SET     avatarID = ?
                    WHERE   userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$avatar->avatarID, $data['userID']]);

            return $avatar->avatarID;
        } catch (SystemException $e) {
            // copy failed; delete avatar
            $editor = new UserAvatarEditor($avatar);
            $editor->delete();
        }

        return 0;
    }
}
