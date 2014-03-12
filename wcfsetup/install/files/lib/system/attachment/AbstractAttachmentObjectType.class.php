<?php
namespace wcf\system\attachment;
use wcf\system\attachment\IAttachmentObjectType;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Provides a default implementation for attachment object types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.attachment
 * @category	Community Framework
 */
abstract class AbstractAttachmentObjectType implements IAttachmentObjectType {
	/**
	 * @see	\wcf\system\attachment\IAttachmentObjectType::getMaxSize()
	 */
	public function getMaxSize() {
		return WCF::getSession()->getPermission('user.attachment.maxSize');
	}
	
	/**
	 * @see	\wcf\system\attachment\IAttachmentObjectType::getAllowedExtensions()
	 */
	public function getAllowedExtensions() {
		return ArrayUtil::trim(explode("\n", WCF::getSession()->getPermission('user.attachment.allowedExtensions')));
	}
	
	/**
	 * @see	\wcf\system\attachment\IAttachmentObjectType::getMaxCount()
	 */
	public function getMaxCount() {
		return WCF::getSession()->getPermission('user.attachment.maxCount');
	}
	
	/**
	 * @see	\wcf\system\attachment\IAttachmentObjectType::canViewPreview()
	 */
	public function canViewPreview($objectID) {
		return $this->canDownload($objectID);
	}
	
	/**
	 * @see	\wcf\system\attachment\IAttachmentObjectType::getObject()
	 */
	public function getObject($objectID) {
		return null;
	}
	
	/**
	 * @see	\wcf\system\attachment\IAttachmentObjectType::getObject()
	 */
	public function cacheObjects(array $objectIDs) {}
}
