<?php
namespace wcf\system\html\output\node;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\WCF;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * Processes user mentions.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       3.0
 */
class HtmlOutputNodeWoltlabMention extends AbstractHtmlOutputNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'woltlab-mention';
	
	/**
	 * @var	UserProfile[]
	 */
	protected $userProfiles;
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		if ($this->outputType === 'text/html' || $this->outputType === 'text/simplified-html') {
			$this->userProfiles = [];
			
			$userIds = [];
			/** @var \DOMElement $element */
			foreach ($elements as $element) {
				$userId = intval($element->getAttribute('data-user-id'));
				$username = StringUtil::trim($element->getAttribute('data-username'));
				
				if ($userId === 0 || $username === '') {
					DOMUtil::removeNode($element);
					continue;
				}
				
				$userIds[] = $userId;
				$nodeIdentifier = StringUtil::getRandomID();
				$htmlNodeProcessor->addNodeData($this, $nodeIdentifier, ['userId' => $userId, 'username' => $username]);
				
				$htmlNodeProcessor->renameTag($element, 'wcfNode-' . $nodeIdentifier);
			}
			
			if (!empty($userIds)) {
				$this->userProfiles = UserProfileRuntimeCache::getInstance()->getObjects($userIds);
			}
		}
		else if ($this->outputType === 'text/plain') {
			/** @var \DOMElement $element */
			foreach ($elements as $element) {
				$htmlNodeProcessor->replaceElementWithText($element, '@' . $element->getAttribute('data-username'), false);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function replaceTag(array $data) {
		WCF::getTPL()->assign([
			'username' => $data['username'],
			'userId' => $data['userId'],
			'userProfile' => $this->userProfiles[$data['userId']]
		]);
		
		return WCF::getTPL()->fetch('htmlNodeWoltlabMention');
	}
}
