<?php
namespace wcf\system\bbcode;
use wcf\data\attachment\GroupedAttachmentList;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Parses the [attach] bbcode tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 */
class AttachmentBBCode extends AbstractBBCode {
	/**
	 * list of attachments
	 * @var	GroupedAttachmentList
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
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		// get attachment id
		$attachmentID = 0;
		if (isset($openingTag['attributes'][0])) {
			$attachmentID = $openingTag['attributes'][0];
		}
		
		$hasParentLink = false;
		if (!empty($closingTag['__parents'])) {
			/** @var \DOMElement $parent */
			foreach ($closingTag['__parents'] as $parent) {
				if ($parent->nodeName === 'a') {
					$hasParentLink = true;
					break;
				}
			}
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
			if ($attachment->showAsImage() && $attachment->canViewPreview() && ($parser->getOutputType() == 'text/html' || $parser->getOutputType() == 'text/simplified-html')) {
				// image
				$alignment = (isset($openingTag['attributes'][1]) ? $openingTag['attributes'][1] : '');
				$thumbnail = (isset($openingTag['attributes'][2]) ? $openingTag['attributes'][2] : false);
				
				// backward compatibility, check if width is larger than thumbnail's width to display full version
				if (is_numeric($thumbnail)) {
					if ($thumbnail == 0) {
						$thumbnail = true;
					}
					else {
						// true if supplied width is smaller or equal to thumbnail's width
						$thumbnail = ($attachment->thumbnailWidth >= $thumbnail) ? true : false;
					}
				}
				else if ($thumbnail === 'false') {
					$thumbnail = false;
				}
				else if ($thumbnail !== false) {
					$thumbnail = true;
				}
				
				// always use thumbnail in simplified version
				if ($parser->getOutputType() == 'text/simplified-html') {
					$thumbnail = true;
				}
				
				// check if width is valid and the original is accessible by viewer
				if (!$thumbnail && !$attachment->canDownload()) {
					$thumbnail = false;
				}
				
				if (!$thumbnail) {
					$class = '';
					if ($alignment == 'left' || $alignment == 'right') {
						$class = 'messageFloatObject'.ucfirst($alignment);
					}
					
					$source = StringUtil::encodeHTML(LinkHandler::getInstance()->getLink('Attachment', ['object' => $attachment]));
					$title = StringUtil::encodeHTML($attachment->filename);
					
					if ($parser instanceof HtmlBBCodeParser && $parser->getIsGoogleAmp()) {
						$result = '<amp-img src="' . $source . '" width="'.$attachment->width.'" height="'.$attachment->height.'" layout="responsive" alt="">';
					}
					else {
						$result = '<img src="' . $source . '" alt="">';
					}
					
					if (!$hasParentLink && ($attachment->width > ATTACHMENT_THUMBNAIL_WIDTH || $attachment->height > ATTACHMENT_THUMBNAIL_HEIGHT)) {
						$result = '<a href="' . $source . '" title="' . $title . '" class="embeddedAttachmentLink jsImageViewer' . ($class ? ' '.$class : '') . '">' . $result . '</a>';
					}
					else {
						$result = '<span title="' . $title . '"' . ($class ? (' class="' . $class . '"') : '') . '>' . $result . '</span>';
					}
				}
				else {
					$linkParameters = [
						'object' => $attachment
					];
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
					
					if ($parser instanceof HtmlBBCodeParser && $parser->getIsGoogleAmp()) {
						$result = '<amp-img src="' . StringUtil::encodeHTML(LinkHandler::getInstance()->getLink('Attachment', $linkParameters)) . '"' . ($imageClasses ? ' class="' . $imageClasses . '"' : '') . ' width="' . ($attachment->hasThumbnail() ? $attachment->thumbnailWidth : $attachment->width) . '" height="' . ($attachment->hasThumbnail() ? $attachment->thumbnailHeight : $attachment->height) . '" layout="flex-item" alt="">';
					}
					else {
						$result = '<img src="' . StringUtil::encodeHTML(LinkHandler::getInstance()->getLink('Attachment', $linkParameters)) . '"' . ($imageClasses ? ' class="' . $imageClasses . '"' : '') . ' style="width: ' . ($attachment->hasThumbnail() ? $attachment->thumbnailWidth : $attachment->width) . 'px; height: ' . ($attachment->hasThumbnail() ? $attachment->thumbnailHeight : $attachment->height) . 'px;" alt="">';
					}
					if (!$hasParentLink && $attachment->hasThumbnail() && $attachment->canDownload()) {
						$result = '<a href="'.StringUtil::encodeHTML(LinkHandler::getInstance()->getLink('Attachment', ['object' => $attachment])).'" title="'.StringUtil::encodeHTML($attachment->filename).'" class="embeddedAttachmentLink jsImageViewer' . ($class ? ' '.$class : '') . '">'.$result.'</a>';
					}
				}
				
				return $result;
			}
			else if (substr($attachment->fileType, 0, 6) === 'video/') {
				return WCF::getTPL()->fetch('__videoAttachmentBBCode', 'wcf', [
					'attachment' => $attachment,
					'attachmentIdentifier' => StringUtil::getRandomID()
				]);
			}
			else {
				// file
				return StringUtil::getAnchorTag(LinkHandler::getInstance()->getLink('Attachment', [
					'object' => $attachment
				]), $attachment->filename);
			}
		}
		
		// fallback
		return StringUtil::getAnchorTag(LinkHandler::getInstance()->getLink('Attachment', [
			'id' => $attachmentID
		]));
	}
	
	/**
	 * Sets the attachment list.
	 * 
	 * @param	GroupedAttachmentList	$attachmentList
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
