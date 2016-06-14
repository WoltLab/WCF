<?php
namespace wcf\system\message\embedded\object;
use wcf\data\attachment\AttachmentList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\util\ArrayUtil;

/**
 * IMessageEmbeddedObjectHandler implementation for attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Message\Embedded\Object
 */
class AttachmentMessageEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler {
	/**
	 * @inheritDoc
	 */
	public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData) {
		if (empty($embeddedData['attach'])) {
			return [];
		}
		
		$attachmentIDs = [];
		for ($i = 0, $length = count($embeddedData['attach']); $i < $length; $i++) {
			$attributes = $embeddedData['attach'][$i];
			$attachmentID = (!empty($attributes[0])) ? intval($attributes[0]) : 0;
			
			if ($attachmentID > 0) {
				$attachmentIDs[] = $attachmentID;
			}
		}
		
		if (!empty($attachmentIDs)) {
			$attachmentList = new AttachmentList();
			$attachmentList->getConditionBuilder()->add("attachment.attachmentID IN (?)", [$attachmentIDs]);
			$attachmentList->readObjectIDs();
			
			return $attachmentList->getObjectIDs();
		}
		
		return [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function loadObjects(array $objectIDs) {
		$attachmentList = new AttachmentList();
		$attachmentList->setObjectIDs($objectIDs);
		$attachmentList->readObjects();
		
		// group attachments by object type
		$groupedAttachments = [];
		foreach ($attachmentList->getObjects() as $attachment) {
			if (!isset($groupedAttachments[$attachment->objectTypeID])) $groupedAttachments[$attachment->objectTypeID] = [];
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
