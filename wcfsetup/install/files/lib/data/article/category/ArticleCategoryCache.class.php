<?php
namespace wcf\data\article\category;
use wcf\data\article\Article;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the article category cache.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article\Category
 * @since	3.0
 */
class ArticleCategoryCache extends SingletonFactory {
	/**
	 * number of total articles
	 * @var	integer[]
	 */
	protected $articles;
	
	/**
	 * Calculates the number of articles.
	 */
	protected function initArticles() {
		$sql = "SELECT		COUNT(*) AS count, categoryID
			FROM		wcf" . WCF_N . "_article
			WHERE           publicationStatus = ?
			GROUP BY	categoryID";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([Article::PUBLISHED]);
		$this->articles = $statement->fetchMap('categoryID', 'count');
	}
	
	/**
	 * Returns the number of articles in the category with the given id.
	 * 
	 * @param	integer		$categoryID
	 * @return	integer
	 */
	public function getArticles($categoryID) {
		if ($this->articles === null) {
			$this->initArticles();
		}
		
		if (isset($this->articles[$categoryID])) return $this->articles[$categoryID];
		return 0;
	}
}
