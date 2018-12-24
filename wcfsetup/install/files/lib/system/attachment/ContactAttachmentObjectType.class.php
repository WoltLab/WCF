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
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Attachment
 * @since       3.2
 * 
 * @method ContactAttachment getObject($objectID)
 */
class ContactAttachmentObjectType extends AbstractAttachmentObjectType {
	/**
	 * @inheritDoc
	 */
	public function getMaxSize() {
		return WCF::getSession()->getPermission('user.contactForm.attachment.maxSize');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAllowedExtensions() {
		return ArrayUtil::trim(explode("\n", WCF::getSession()->getPermission('user.contactForm.attachment.allowedExtensions')));
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMaxCount() {
		return WCF::getSession()->getPermission('user.contactForm.attachment.maxCount');
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDownload($objectID) {
		if (!CONTACT_FORM_ENABLE_ATTACHMENTS) return false;
		
		// The administrator does not require the access key in order to view the attachment.
		if (!WCF::getSession()->getPermission('admin.contact.canManageContactForm')) return false;
		
		return true;
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function canViewPreview($objectID) {
		return $this->canDownload($objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function canUpload($objectID, $parentObjectID = 0) {
		if (!CONTACT_FORM_ENABLE_ATTACHMENTS) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDelete($objectID) {
		return $this->canUpload($objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function cacheObjects(array $objectIDs) {
		$objectList = new ContactAttachmentList();
		$objectList->setObjectIDs($objectIDs);
		$objectList->readObjects();
		
		$this->setCachedObjects($objectList->getObjects());
	}
}
