<?php
namespace wcf\system\message\embedded\object;
use wcf\data\attachment\AttachmentList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\util\ArrayUtil;

/**
 * IMessageEmbeddedObjectHandler implementation for attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.message.embedded.object
 * @category	Community Framework
 */
class AttachmentMessageEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler {
	/**
	 * @see	\wcf\system\message\embedded\object\IMessageEmbeddedObjectHandler::parseMessage()
	 */
	public function parseMessage($message) {
		$parsedAttachmentIDs = array_unique(ArrayUtil::toIntegerArray(array_merge(self::getFirstParameters($message, 'attach'), self::getTextParameters($message, 'attach'))));
		if (!empty($parsedAttachmentIDs)) {
			$attachmentIDs = array();
			foreach ($parsedAttachmentIDs as $parsedAttachmentID) {
				if ($parsedAttachmentID) $attachmentIDs[] = $parsedAttachmentID;
			}
			
			if (!empty($attachmentIDs)) {
				$attachmentList = new AttachmentList();
				$attachmentList->getConditionBuilder()->add("attachment.attachmentID IN (?)", array($attachmentIDs));
				$attachmentList->readObjectIDs();
				
				return $attachmentList->getObjectIDs();
			}
		}
		
		return false;
	}
	
	/**
	 * @see	\wcf\system\message\embedded\object\IMessageEmbeddedObjectHandler::loadObjects()
	 */
	public function loadObjects(array $objectIDs) {
		$attachmentList = new AttachmentList();
		$attachmentList->setObjectIDs($objectIDs);
		$attachmentList->readObjects();
		
		// group attachments by object type
		$groupedAttachments = array();
		foreach ($attachmentList->getObjects() as $attachment) {
			if (!isset($groupedAttachments[$attachment->objectTypeID])) $groupedAttachments[$attachment->objectTypeID] = array();
			$groupedAttachments[$attachment->objectTypeID][] = $attachment;
		}
		
		// check permissions
		foreach ($groupedAttachments as $objectTypeID => $attachments) {
			$processor = ObjectTypeCache::getInstance()->getObjectType($objectTypeID)->getProcessor();
			if ($processor !== null) {
				$processor->setPermissions($attachments);
			}
		}
		
		return $attachmentList->getObjects();
	}
}
