<?php

namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\system\WCF;

/**
 * Unbans users and enables disabled avatars and disabled signatures.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserBanCronjob extends AbstractCronjob
{
    /**
     * @inheritDoc
     */
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        // unban users
        $sql = "UPDATE  wcf1_user
                SET     banned = ?,
                        banExpires = ?
                WHERE   banned = ?
                    AND banExpires <> ?
                    AND banExpires <= ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            0,
            0,
            1,
            0,
            TIME_NOW,
        ]);

        // enable avatars
        $sql = "UPDATE  wcf1_user
                SET     disableAvatar = ?,
                        disableAvatarExpires = ?
                WHERE   disableAvatar = ?
                    AND disableAvatarExpires <> ?
                    AND disableAvatarExpires <= ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            0,
            0,
            1,
            0,
            TIME_NOW,
        ]);

        // enable signatures
        $sql = "UPDATE  wcf1_user
                SET     disableSignature = ?,
                        disableSignatureExpires = ?
                WHERE   disableSignature = ?
                    AND disableSignatureExpires <> ?
                    AND disableSignatureExpires <= ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            0,
            0,
            1,
            0,
            TIME_NOW,
        ]);

        // enable cover photos
        $sql = "UPDATE  wcf1_user
                SET     disableCoverPhoto = ?,
                        disableCoverPhotoExpires = ?
                WHERE   disableCoverPhoto = ?
                    AND disableCoverPhotoExpires <> ?
                    AND disableCoverPhotoExpires <= ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            0,
            0,
            1,
            0,
            TIME_NOW,
        ]);
    }
}
