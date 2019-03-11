<?php
namespace wcf\system\attachment;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\WCF;

/**
 * Attachment object type implementation for posts.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Attachment
 * @since       5.2
 * 
 * @method	UserProfile	getObject($objectID)
 */
class SignatureAttachmentObjectType extends AbstractAttachmentObjectType {
	/**
	 * @inheritDoc
	 */
	public function canDownload($objectID) {
		if ($objectID) {
			$userProfile = UserProfileRuntimeCache::getInstance()->getObject($objectID);
			
			if (!MODULE_USER_SIGNATURE) return false;
			if ($userProfile->disableSignature) return false;
			if ($userProfile->banned) return false;
			
			return true;
		}
		
		return false;
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
		if (!$objectID || $objectID != WCF::getUser()->userID) {
			return false;
		}
		
		$userProfile = UserProfileRuntimeCache::getInstance()->getObject($objectID);
		
		if (!MODULE_USER_SIGNATURE) {
			return false;
		}
		
		if ($userProfile->disableSignature) {
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
		$this->setCachedObjects(UserProfileRuntimeCache::getInstance()->getObjects($objectIDs));
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function setPermissions(array $attachments) {
		$objectIDs = [];
		foreach ($attachments as $attachment) {
			// set default permissions
			$attachment->setPermissions([
				'canDownload' => false,
				'canViewPreview' => false
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
				if (!$userProfile->showSignature()) continue;
				
				$attachment->setPermissions([
					'canDownload' => true,
					'canViewPreview' => true
				]);
			}
		}
	}
}
