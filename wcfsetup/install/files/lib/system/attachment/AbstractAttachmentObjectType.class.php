<?php
namespace wcf\system\attachment;
use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Provides a default implementation for attachment object types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Attachment
 */
abstract class AbstractAttachmentObjectType implements IAttachmentObjectType {
	/**
	 * cached objects
	 * @var	DatabaseObject[]
	 */
	protected $cachedObjects = [];
	
	/**
	 * @inheritDoc
	 */
	public function getMaxSize() {
		return WCF::getSession()->getPermission('user.attachment.maxSize');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAllowedExtensions() {
		return ArrayUtil::trim(explode("\n", WCF::getSession()->getPermission('user.attachment.allowedExtensions')));
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMaxCount() {
		return WCF::getSession()->getPermission('user.attachment.maxCount');
	}
	
	/**
	 * @inheritDoc
	 */
	public function canViewPreview($objectID) {
		return $this->canDownload($objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObject($objectID) {
		if (isset($this->cachedObjects[$objectID])) return $this->cachedObjects[$objectID];
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setCachedObjects(array $objects) {
		foreach ($objects as $id => $object) {
			$this->cachedObjects[$id] = $object;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function cacheObjects(array $objectIDs) {}
	
	/**
	 * @inheritDoc
	 */
	public function setPermissions(array $attachments) {
		foreach ($attachments as $attachment) {
			$attachment->setPermissions([
				'canDownload' => $this->canDownload($attachment->objectID),
				'canViewPreview' => $this->canViewPreview($attachment->objectID)
			]);
		}
	}
}
