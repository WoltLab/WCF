<?php
namespace wcf\system\html\output\node;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\html\node\AbstractHtmlNode;
use wcf\system\html\node\HtmlNodeProcessor;
use wcf\system\WCF;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlOutputNodeWoltlabMention extends AbstractHtmlNode {
	protected $tagName = 'woltlab-mention';
	
	/**
	 * @var	UserProfile[]
	 */
	protected $userProfiles;
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, HtmlNodeProcessor $htmlNodeProcessor) {
		$this->userProfiles = [];
		
		$userIds = [];
		foreach ($elements as $element) {
			$userId = ($element->hasAttribute('data-user-id')) ? intval($element->getAttribute('data-user-id')) : 0;
			$username = ($element->hasAttribute('data-username')) ? StringUtil::trim($element->getAttribute('data-username')) : '';
			
			if ($userId === 0 || $username === '') {
				DOMUtil::removeNode($element);
				continue;
			}
			
			$userIds[] = $userId;
			$nodeIdentifier = StringUtil::getRandomID();
			$htmlNodeProcessor->addNodeData($this, $nodeIdentifier, [
				'userId' => $userId,
				'username' => $username
			]);
			
			$htmlNodeProcessor->renameTag($element, 'wcfNode-' . $nodeIdentifier);
		}
		
		if (!empty($userIds)) {
			$this->userProfiles = UserProfileRuntimeCache::getInstance()->getObjects($userIds);
		}
	}
	
	public function replaceTag(array $data) {
		WCF::getTPL()->assign([
			'username' => $data['username'],
			'userId' => $data['userId'],
			'userProfile' => $this->userProfiles[$data['userId']]
		]);
		
		return WCF::getTPL()->fetch('htmlNodeWoltlabMention');
	}
}
