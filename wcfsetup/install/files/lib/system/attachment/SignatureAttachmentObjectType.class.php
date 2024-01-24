<?php

namespace wcf\system\attachment;

use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Attachment object type implementation for posts.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 *
 * @method  UserProfile getObject($objectID)
 */
class SignatureAttachmentObjectType extends AbstractAttachmentObjectType
{
    /**
     * @inheritDoc
     */
    public function canDownload($objectID)
    {
        if (!MODULE_USER_SIGNATURE) {
            return false;
        }
        if ($objectID) {
            $userProfile = UserProfileRuntimeCache::getInstance()->getObject($objectID);

            if ($this->canEditUser($userProfile)) {
                return true;
            }
            if ($userProfile->disableSignature) {
                return false;
            }
            if ($userProfile->banned) {
                return false;
            }

            return true;
        }

        return $this->canAddUser();
    }

    /**
     * @inheritDoc
     */
    public function canViewPreview($objectID)
    {
        return $this->canDownload($objectID);
    }

    /**
     * @inheritDoc
     */
    public function canUpload($objectID, $parentObjectID = 0)
    {
        if (!MODULE_USER_SIGNATURE) {
            return false;
        }

        if (!$objectID) {
            return $this->canAddUser();
        }
        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($objectID);
        if ($this->canEditUser($userProfile)) {
            return true;
        }

        if ($objectID != WCF::getUser()->userID) {
            return false;
        }

        if ($userProfile->disableSignature) {
            return false;
        }
        if (!$userProfile->getPermission('user.signature.attachment.canUpload')) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function canDelete($objectID)
    {
        return $this->canUpload($objectID);
    }

    /**
     * @inheritDoc
     */
    public function cacheObjects(array $objectIDs)
    {
        $this->setCachedObjects(UserProfileRuntimeCache::getInstance()->getObjects($objectIDs));
    }

    /**
     * @inheritDoc
     */
    public function getMaxSize()
    {
        return WCF::getSession()->getPermission('user.signature.attachment.maxSize');
    }

    /**
     * @inheritDoc
     */
    public function getAllowedExtensions()
    {
        return ArrayUtil::trim(\explode(
            "\n",
            WCF::getSession()->getPermission('user.signature.attachment.allowedExtensions')
        ));
    }

    /**
     * @inheritDoc
     */
    public function getMaxCount()
    {
        return WCF::getSession()->getPermission('user.signature.attachment.maxCount');
    }

    /**
     * @inheritDoc
     */
    public function setPermissions(array $attachments)
    {
        $objectIDs = [];
        foreach ($attachments as $attachment) {
            // set default permissions
            $attachment->setPermissions([
                'canDownload' => false,
                'canViewPreview' => false,
            ]);

            if ($this->getObject($attachment->objectID) === null) {
                $objectIDs[] = $attachment->objectID;
            }
        }

        if (!empty($objectIDs)) {
            $this->cacheObjects($objectIDs);
        }

        foreach ($attachments as $attachment) {
            if (($userProfile = $this->getObject($attachment->objectID)) !== null) {
                if (!$userProfile->showSignature()) {
                    continue;
                }

                $attachment->setPermissions([
                    'canDownload' => true,
                    'canViewPreview' => true,
                ]);
            }
        }
    }

    private function canAddUser(): bool
    {
        return WCF::getSession()->getPermission('admin.user.canAddUser');
    }

    private function canEditUser(UserProfile $userProfile): bool
    {
        return WCF::getSession()->getPermission('admin.user.canEditUser')
            && UserGroup::isAccessibleGroup($userProfile->getGroupIDs());
    }
}
