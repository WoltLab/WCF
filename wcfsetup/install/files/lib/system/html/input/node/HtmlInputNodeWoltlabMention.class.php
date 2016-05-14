<?php
namespace wcf\system\html\input\node;
use wcf\system\html\node\AbstractHtmlNode;
use wcf\system\html\node\HtmlNodeProcessor;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlInputNodeWoltlabMention extends AbstractHtmlNode {
	protected $tagName = 'woltlab-mention';
	
	public function process(array $elements, HtmlNodeProcessor $htmlNodeProcessor) {
		$userIds = [];
		
		/** @var \DOMElement $mention */
		foreach ($elements as $mention) {
			$userId = intval($mention->getAttribute('data-user-id'));
			if ($userId) {
				$userIds[] = $userId;
			}
		}
		
		if (!empty($userIds)) {
			
		}
	}
	
	public function replaceTag(array $data) {
		return null;
	}
}
