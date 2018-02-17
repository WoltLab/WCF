<?php
namespace wcf\data\article;
use wcf\data\article\category\ArticleCategory;
use wcf\system\WCF;

/**
 * Represents a list of accessible articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article
 * @since	3.0
 */
class AccessibleArticleList extends ViewableArticleList {
	/**
	 * Creates a new AccessibleArticleList object.
	 */
	public function __construct() {
		parent::__construct();
		
		// get accessible categories
		$accessibleCategoryIDs = ArticleCategory::getAccessibleCategoryIDs();
		if (empty($accessibleCategoryIDs)) {
			$this->getConditionBuilder()->add('1=0');
		}
		else {
			$this->getConditionBuilder()->add('article.categoryID IN (?)', [$accessibleCategoryIDs]);
			$this->getConditionBuilder()->add('article.publicationStatus = ?', [Article::PUBLISHED]);
			
			if (!WCF::getSession()->getPermission('admin.content.article.canManageArticle')) {
				$this->getConditionBuilder()->add('article.isDeleted = ?', [0]);
			}
		}
	}
}
