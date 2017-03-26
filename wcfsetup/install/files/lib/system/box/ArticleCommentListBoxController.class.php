<?php
namespace wcf\system\box;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\Article;
use wcf\data\comment\ViewableCommentList;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Box controller implementation for a list of article comments.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 */
class ArticleCommentListBoxController extends AbstractCommentListBoxController {
	/**
	 * @inheritDoc
	 */
	protected $objectTypeName = 'com.woltlab.wcf.articleComment';
	
	/**
	 * @inheritDoc
	 */
	protected function applyObjectTypeFilters(ViewableCommentList $commentList) {
		$accessibleCategoryIDs = ArticleCategory::getAccessibleCategoryIDs();
		if (!empty($accessibleCategoryIDs)) {
			$commentList->sqlJoins .= ' INNER JOIN wcf' . WCF_N . '_article_content article_content ON (article_content.articleContentID = comment.objectID)';
			$commentList->sqlJoins .= ' INNER JOIN wcf' . WCF_N . '_article article ON (article.articleID = article_content.articleID)';
			$commentList->sqlSelects = 'article_content.title';
			
			$commentList->getConditionBuilder()->add('article.categoryID IN (?)', [$accessibleCategoryIDs]);
			$commentList->getConditionBuilder()->add('article.publicationStatus = ?', [Article::PUBLISHED]);
			
			// apply language filter
			if (LanguageFactory::getInstance()->multilingualismEnabled() && !empty(WCF::getUser()->getLanguageIDs())) {
				$commentList->getConditionBuilder()->add('(article_content.languageID IN (?) OR article_content.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
			}
		}
		else {
			$commentList->getConditionBuilder()->add('0 = 1');
		}
	}
}
