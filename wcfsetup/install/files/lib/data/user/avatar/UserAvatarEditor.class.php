<?php

namespace wcf\data\user\avatar;

use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;
use wcf\util\ImageUtil;

/**
 * Provides functions to edit avatars.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method static UserAvatar  create(array $parameters = [])
 * @method      UserAvatar  getDecoratedObject()
 * @mixin       UserAvatar
 */
class UserAvatarEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserAvatar::class;

    /**
     * @inheritDoc
     */
    public function delete()
    {
        $sql = "DELETE FROM wcf1_user_avatar
                WHERE       avatarID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->avatarID]);

        $this->deleteFiles();
    }

    /**
     * @inheritDoc
     */
    public static function deleteAll(array $objectIDs = [])
    {
        $sql = "SELECT  *
                FROM    wcf1_user_avatar
                WHERE   avatarID IN (" . \str_repeat('?,', \count($objectIDs) - 1) . "?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($objectIDs);
        while ($avatar = $statement->fetchObject(self::$baseClass)) {
            $editor = new self($avatar);
            $editor->deleteFiles();
        }

        return parent::deleteAll($objectIDs);
    }

    /**
     * Deletes avatar files.
     */
    public function deleteFiles()
    {
        // delete original size
        @\unlink($this->getLocation(null, false));

        if ($this->hasWebP) {
            @\unlink($this->getLocation(null, true));
        }
    }

    /**
     * Creates a WebP variant of the avatar, unless it is a GIF image. If the
     * user uploads a WebP image, this method will create a JPEG variant as a
     * fallback for ancient clients.
     *
     * Will return `true` if a variant has been created.
     *
     * @since 5.4
     */
    public function createAvatarVariant(): bool
    {
        if ($this->hasWebP) {
            return false;
        }

        if ($this->avatarExtension === "gif") {
            // We do not touch GIFs at all.
            return false;
        }

        $outputFilenameWithoutExtension = \preg_replace('~\.[a-z]+$~', '', $this->getLocation());
        $result = ImageUtil::createWebpVariant($this->getLocation(), $outputFilenameWithoutExtension);
        if ($result !== null) {
            $data = ['hasWebP' => 1];

            // A fallback jpeg image was just created.
            if ($result === false) {
                $data['avatarExtension'] = 'jpg';
            }

            $this->update($data);

            return true;
        }

        return false;
    }
}
