<?php
namespace wcf\system\html\input\node;
use wcf\system\html\node\AbstractHtmlNodeProcessor;

/**
 * Processes `<woltlab-mention>`.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Input\Node
 * @since       3.0
 */
class HtmlInputNodeWoltlabMention extends AbstractHtmlInputNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'woltlab-mention';
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		// TODO
		
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
}
