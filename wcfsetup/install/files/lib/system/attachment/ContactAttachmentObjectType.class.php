<?php

namespace wcf\system\attachment;

use wcf\data\contact\attachment\ContactAttachment;
use wcf\data\contact\attachment\ContactAttachmentList;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Attachment object type implementation for messages sent through the contact form.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 * @deprecated  6.2 Contact form attachments are using `ContactFormFileProcessor` instead.
 *
 * @extends AbstractAttachmentObjectType<ContactAttachment>
 */
class ContactAttachmentObjectType extends AbstractAttachmentObjectType
{
    #[\Override]
    public function getMaxSize()
    {
        return WCF::getSession()->getPermission('user.contactForm.attachment.maxSize');
    }

    #[\Override]
    public function getAllowedExtensions()
    {
        return ArrayUtil::trim(\explode(
            "\n",
            WCF::getSession()->getPermission('user.contactForm.attachment.allowedExtensions')
        ));
    }

    #[\Override]
    public function getMaxCount()
    {
        return WCF::getSession()->getPermission('user.contactForm.attachment.maxCount');
    }

    #[\Override]
    public function canDownload(int $objectID)
    {
        if (!CONTACT_FORM_ENABLE_ATTACHMENTS) {
            return false;
        }

        // The administrator does not require the access key in order to view the attachment.
        if (!WCF::getSession()->getPermission('admin.contact.canManageContactForm')) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function canViewPreview(int $objectID)
    {
        return $this->canDownload($objectID);
    }

    #[\Override]
    public function canUpload(int $objectID, int $parentObjectID = 0)
    {
        if (!CONTACT_FORM_ENABLE_ATTACHMENTS) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function canDelete(int $objectID)
    {
        return $this->canUpload($objectID);
    }

    #[\Override]
    public function cacheObjects(array $objectIDs)
    {
        $objectList = new ContactAttachmentList();
        $objectList->setObjectIDs($objectIDs);
        $objectList->readObjects();

        $this->setCachedObjects($objectList->getObjects());
    }
}
