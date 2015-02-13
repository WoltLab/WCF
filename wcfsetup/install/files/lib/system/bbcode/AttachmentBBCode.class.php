<?php
namespace wcf\system\bbcode;
use wcf\data\attachment\GroupedAttachmentList;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\LinkHandler;
use wcf\util\StringUtil;

/**
 * Parses the [attach] bbcode tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
class AttachmentBBCode extends AbstractBBCode {
	/**
	 * list of attachments
	 * @var	\wcf\data\attachment\GroupedAttachmentList
	 * @deprecated
	 */
	protected static $attachmentList = null;
	
	/**
	 * active object id
	 * @var	integer
	 * @deprecated
	 */
	protected static $objectID = 0;
	
	/**
	 * @see	\wcf\system\bbcode\IBBCode::getParsedTag()
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		// get attachment id
		$attachmentID = 0;
		if (isset($openingTag['attributes'][0])) {
			$attachmentID = $openingTag['attributes'][0];
		}
		
		// get embedded object
		$attachment = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.attachment', $attachmentID);
		if ($attachment === null) {
			if (self::$attachmentList !== null) {
				$attachments = self::$attachmentList->getGroupedObjects(self::$objectID);
				if (isset($attachments[$attachmentID])) {
					$attachment = $attachments[$attachmentID];
					
					// mark attachment as embedded
					$attachment->markAsEmbedded();
				}
			}
		}
		
		if ($attachment !== null) {
			if ($attachment->showAsImage() && $attachment->canViewPreview() && $parser->getOutputType() == 'text/html') {
				// image
				$alignment = (isset($openingTag['attributes'][1]) ? $openingTag['attributes'][1] : '');
				$width = (isset($openingTag['attributes'][2]) ? $openingTag['attributes'][2] : 0);
				
				// check if width is valid and the original is accessible by viewer
				if ($width > 0) {
					if ($attachment->canDownload()) {
						// check if width exceeds image width
						if ($width > $attachment->width) {
							$width = $attachment->width;
						}
					}
					else {
						$width = 0;
					}
				}
				
				$result = '';
				if ($width > 0) {
					$class = '';
					if ($alignment == 'left' || $alignment == 'right') {
						$class = 'messageFloatObject'.ucfirst($alignment);
					}
					
					$source = StringUtil::encodeHTML(LinkHandler::getInstance()->getLink('Attachment', array('object' => $attachment)));
					$title = StringUtil::encodeHTML($attachment->filename);
					
					$result = '<a href="' . $source . '" title="' . $title . '" class="embeddedAttachmentLink jsImageViewer' . ($class ? ' '.$class : '') . '"><img src="' . $source . '" style="width: '.$width.'px" alt="" /></a>';
				}
				else {
					$linkParameters = array(
						'object' => $attachment
					);
					if ($attachment->hasThumbnail()) $linkParameters['thumbnail'] = 1;
					
					$class = '';
					if ($alignment == 'left' || $alignment == 'right') {
						$class = 'messageFloatObject'.ucfirst($alignment);
					}
					
					$imageClasses = '';
					if (!$attachment->hasThumbnail()) {
						$imageClasses = 'embeddedAttachmentLink jsResizeImage';
					}
					
					if ($class && (!$attachment->hasThumbnail() || !$attachment->canDownload())) {
						$imageClasses .= ' '.$class;
					}
					
					$result = '<img src="'.StringUtil::encodeHTML(LinkHandler::getInstance()->getLink('Attachment', $linkParameters)).'"'.($imageClasses ? ' class="'.$imageClasses.'"' : '').' style="width: '.($attachment->hasThumbnail() ? $attachment->thumbnailWidth : $attachment->width).'px; height: '.($attachment->hasThumbnail() ? $attachment->thumbnailHeight : $attachment->height).'px;" alt="" />';
					if ($attachment->hasThumbnail() && $attachment->canDownload()) {
						$result = '<a href="'.StringUtil::encodeHTML(LinkHandler::getInstance()->getLink('Attachment', array('object' => $attachment))).'" title="'.StringUtil::encodeHTML($attachment->filename).'" class="embeddedAttachmentLink jsImageViewer' . ($class ? ' '.$class : '') . '">'.$result.'</a>';
					}
				}
				
				return $result;
			}
			else {
				// file
				return StringUtil::getAnchorTag(LinkHandler::getInstance()->getLink('Attachment', array(
					'object' => $attachment
				)), ((!empty($content) && $content != $attachmentID) ? $content : $attachment->filename));
			}
		}
		
		// fallback
		return StringUtil::getAnchorTag(LinkHandler::getInstance()->getLink('Attachment', array(
			'id' => $attachmentID
		)));
	}
	
	/**
	 * Sets the attachment list.
	 * 
	 * @param	\wcf\data\attachment\GroupedAttachmentList	$attachments
	 * @deprecated
	 */
	public static function setAttachmentList(GroupedAttachmentList $attachmentList) {
		self::$attachmentList = $attachmentList;
	}
	
	/**
	 * Sets the active object id.
	 * 
	 * @param	integer		$objectID
	 * @deprecated
	 */
	public static function setObjectID($objectID) {
		self::$objectID = $objectID;
	}
}
