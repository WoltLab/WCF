<?php
namespace wcf\system\html\input\node;
use wcf\system\html\node\AbstractHtmlNode;
use wcf\system\html\node\HtmlNodeProcessor;
use wcf\util\DOMUtil;
use wcf\util\JSON;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlInputNodeImg extends AbstractHtmlNode {
	protected $tagName = 'img';
	
	public function process(array $elements, HtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$class = $element->getAttribute('class');
			if ($class !== 'woltlabAttachment') {
				continue;
			}
			
			$attachmentID = intval($element->getAttribute('data-attachment-id'));
			if (!$attachmentID) {
				continue;
			}
			
			// TODO: add alignment detection
			$alignment = 'none';
			$thumbnail = false;
			
			if (strpos($element->getAttribute('src'), 'thumbnail=1') !== false) {
				$thumbnail = true;
			}
			
			$attributes = [
				$attachmentID,
				$alignment,
				$thumbnail
			];
			
			$newElement = $element->ownerDocument->createElement('woltlab-metacode');
			$newElement->setAttribute('data-name', 'attach');
			$newElement->setAttribute('data-attributes', base64_encode(JSON::encode($attributes)));
			DOMUtil::replaceElement($element, $newElement, false);
		}
	}
}
