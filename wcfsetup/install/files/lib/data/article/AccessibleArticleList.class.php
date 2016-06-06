<?php
namespace wcf\data\article;
use wcf\data\article\category\ArticleCategory;

/**
 * Represents a list of accessible articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.article
 * @category	Community Framework
 * @since	2.2
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
			$this->getConditionBuilder()->add('article.publicationStatus = ?', [1]);
		}
	}
}
