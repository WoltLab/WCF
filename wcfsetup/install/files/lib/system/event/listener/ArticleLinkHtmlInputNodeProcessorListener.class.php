<?php
namespace wcf\system\event\listener;
use wcf\data\article\content\ArticleContentList;
use wcf\data\article\AccessibleArticleList;
use wcf\system\html\input\node\HtmlInputNodeProcessor;
use wcf\system\request\LinkHandler;

/**
 * Parses URLs of articles.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
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
		
		$regex = $this->getRegexFromLink(LinkHandler::getInstance()->getLink('Article', [
			'forceFrontend' => true
		]));
		$articleContentIDs = $this->getObjectIDs($eventObj, $regex);
		
		if (!empty($articleContentIDs)) {
			// read linked article contents
			$articleContentList = new ArticleContentList();
			$articleContentList->getConditionBuilder()->add('article_content.articleContentID IN (?)', [$articleContentIDs]);
			$articleContentList->readObjects();
			
			// collect ids of the articles
			$articleIDs = [];
			foreach ($articleContentList as $articleContent) {
				$articleIDs[] = $articleContent->articleID;
			}
			
			if (!empty($articleIDs)) {
				// read the accessible articles of the ones that are linked
				$articleList = new AccessibleArticleList();
				$articleList->getConditionBuilder()->add('article.articleID IN (?)', [array_unique($articleIDs)]);
				$articleList->readObjects();
				
				// filter the article contents by accessibility
				$articleContents = [];
				foreach ($articleContentList as $articleContent) {
					if ($articleList->search($articleContent->articleID) !== null) {
						$articleContents[$articleContent->articleContentID] = $articleContent;
					}
				}
				
				$this->setObjectTitles($eventObj, $regex, $articleContents);
			}
		}
	}
}
