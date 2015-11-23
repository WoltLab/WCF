<?php
namespace wcf\system\html\input\node;

use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

class HtmlInputNodeWoltlabMention implements IHtmlInputNode {
	/**
	 * @var MessageEmbeddedObjectManager
	 */
	protected $messageEmbeddedObjectManager;
	
	public function __construct(MessageEmbeddedObjectManager $messageEmbeddedObjectManager) {
		$this->messageEmbeddedObjectManager = $messageEmbeddedObjectManager;
	}
	
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
