<?php
namespace wcf\system\event\listener;
use wcf\data\article\AccessibleArticleList;
use wcf\system\html\input\node\HtmlInputNodeProcessor;
use wcf\system\request\LinkHandler;

/**
 * Parses URLs of articles.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Event\Listener
 * @since	3.1
 */
class ArticleLinkHtmlInputNodeProcessorListener extends AbstractHtmlInputNodeProcessorListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		/** @var HtmlInputNodeProcessor $eventObj */
		
		$regex = $this->getRegexFromLink(LinkHandler::getInstance()->getLink('Article'));
		$articleIDs = $this->getObjectIDs($eventObj, $regex);
		
		if (!empty($articleIDs)) {
			$articleList = new AccessibleArticleList();
			$articleList->getConditionBuilder()->add('article.articleID IN (?)', [$articleIDs]);
			$articleList->readObjects();
			
			$this->setObjectTitles($eventObj, $regex, $articleList->getObjects());
		}
	}
}
