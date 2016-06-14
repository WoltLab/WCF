<?php
namespace wcf\data\article;

/**
 * Represents a list of articles for RSS feeds.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article
 * @since	3.0
 *
 * @method	FeedArticle	current()
 * @method	FeedArticle[]	getObjects()
 * @method	FeedArticle|null	search($objectID)
 */
class FeedArticleList extends CategoryArticleList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = FeedArticle::class;
	
	/**
	 * Creates a new FeedArticleList object.
	 *
	 * @param	integer         $categoryID
	 */
	public function __construct($categoryID = 0) {
		if ($categoryID) {
			parent::__construct($categoryID, true);
		}
		else {
			AccessibleArticleList::__construct();
		}
	}
}
