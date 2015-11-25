<?php
namespace wcf\system\html\input\node;

class HtmlInputNodeWoltlabMention implements IHtmlInputNode {
	public function process(HtmlInputNodeProcessor $htmlInputNodeProcessor) {
		$userIds = [];
		
		/** @var \DOMElement $mention */
		foreach ($htmlInputNodeProcessor->getDocument()->getElementsByTagName('woltlab-mention') as $mention) {
			$userId = intval($mention->getAttribute('data-user-id'));
			if ($userId) {
				$userIds[] = $userId;
			}
		}
		
		if (!empty($userIds)) {
			
		}
	}
}
