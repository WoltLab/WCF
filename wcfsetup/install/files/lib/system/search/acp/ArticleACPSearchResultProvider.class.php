<?php
namespace wcf\system\search\acp;
use wcf\data\article\content\ArticleContentList;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search result provider implementation for cms articles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search\Acp
 */
class ArticleACPSearchResultProvider implements IACPSearchResultProvider {
	/**
	 * @inheritDoc
	 */
	public function search($query) {
		if (!WCF::getSession()->getPermission('admin.content.article.canManageArticle')) {
			return [];
		}
		
		$results = [];
		
		$contentList = new ArticleContentList();
		$contentList->getConditionBuilder()->add('article_content.title LIKE ?', ['%'.$query.'%']);
		$contentList->sqlLimit = 10;
		$contentList->sqlOrderBy = 'article_content.title';
		$contentList->readObjects();
		foreach ($contentList as $content) {
			$results[] = new ACPSearchResult($content->title, LinkHandler::getInstance()->getLink('ArticleEdit', [
				'id' => $content->articleID,
			]));
		}
		
		return $results;
	}
}
