<?php
namespace wcf\system\html\output\node;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileCache;
use wcf\system\html\output\HtmlOutputNodeProcessor;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlOutputNodeWoltlabMention implements IHtmlOutputNode {
	/**
	 * @var UserProfile[]
	 */
	protected $userProfiles;
	
	public function process(HtmlOutputNodeProcessor $htmlOutputNodeProcessor) {
		$this->userProfiles = [];
		
		$userIds = [];
		$elements = $htmlOutputNodeProcessor->getDocument()->getElementsByTagName('woltlab-mention');
		while ($elements->length) {
			/** @var \DOMElement $mention */
			$mention = $elements->item(0);
			
			$userId = ($mention->hasAttribute('data-user-id')) ? intval($mention->getAttribute('data-user-id')) : 0;
			$username = ($mention->hasAttribute('data-username')) ? StringUtil::trim($mention->getAttribute('data-username')) : '';
			
			if ($userId === 0 || $username === '') {
				$mention->parentNode->removeChild($mention);
				continue;
			}
			
			$userIds[] = $userId;
			$nodeIdentifier = StringUtil::getRandomID();
			$htmlOutputNodeProcessor->addNodeData($this, $nodeIdentifier, [
				'userId' => $userId,
				'username' => $username
			]);
			
			$htmlOutputNodeProcessor->renameTag($mention, 'wcfNode-' . $nodeIdentifier);
		}
		
		if (!empty($userIds)) {
			$this->userProfiles = UserProfileCache::getInstance()->getUserProfiles($userIds);
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
