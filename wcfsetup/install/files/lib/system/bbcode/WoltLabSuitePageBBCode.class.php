<?php
namespace wcf\system\bbcode;
use wcf\data\page\Page;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\util\StringUtil;

/**
 * Parses the [wsp] bbcode tag.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 * @since       3.0
 */
class WoltLabSuitePageBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		$pageID = (!empty($openingTag['attributes'][0])) ? intval($openingTag['attributes'][0]) : 0;
		if (!$pageID) {
			return '';
		}
		
		$title = (!empty($openingTag['attributes'][1])) ? StringUtil::trim($openingTag['attributes'][1]) : '';
		
		/** @var Page $page */
		$page = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.page', $pageID);
		if ($page !== null) {
			return StringUtil::getAnchorTag($page->getLink(), $title ?: $page->getTitle());
		}
		
		return '';
	}
}
