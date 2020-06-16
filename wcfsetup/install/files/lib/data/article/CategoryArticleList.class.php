<?php
namespace wcf\data\article;
use wcf\data\article\category\ArticleCategory;
use wcf\system\WCF;

/**
 * Represents a list of articles in a specific category.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article
 * @since	3.0
 */
class CategoryArticleList extends AccessibleArticleList {
	/** @noinspection PhpMissingParentConstructorInspection */
	/**
	 * Creates a new CategoryArticleList object.
	 *
	 * @param	integer|integer[]       $categoryID
	 * @param       boolean                 $includeChildCategories
	 * @throws      \InvalidArgumentException
	 */
	public function __construct($categoryID, $includeChildCategories = false) {
		ViewableArticleList::__construct();
		
		if (!is_array($categoryID)) {
			$categoryIDs = $fetchChildCategories = [$categoryID];
		}
		else {
			$categoryIDs = $fetchChildCategories = $categoryID;
		}
		
		if ($includeChildCategories) {
			foreach ($fetchChildCategories as $categoryID) {
				$category = ArticleCategory::getCategory($categoryID);
				if ($category === null) {
					throw new \InvalidArgumentException("invalid category id '".$categoryID."' given");
				}
				foreach ($category->getAllChildCategories() as $category) {
					if ($category->isAccessible()) {
						$categoryIDs[] = $category->categoryID;
					}
				}
			}
		}
		
		$this->getConditionBuilder()->add('article.categoryID IN (?)', [$categoryIDs]);
		$this->getConditionBuilder()->add('article.publicationStatus = ?', [Article::PUBLISHED]);
		
		if (!WCF::getSession()->getPermission('admin.content.article.canManageArticle')) {
			$this->getConditionBuilder()->add('article.isDeleted = ?', [0]);
		}
	}
}
