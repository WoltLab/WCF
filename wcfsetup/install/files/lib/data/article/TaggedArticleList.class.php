<?php
namespace wcf\data\article;
use wcf\data\tag\Tag;
use wcf\system\tagging\TagEngine;

/**
 * Represents a list of tagged articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article
 * @since	3.0
 */
class TaggedArticleList extends AccessibleArticleList {
	/**
	 * Creates a new CategoryArticleList object.
	 *
	 * @param Tag|Tag[] $tags
	 */
	public function __construct($tags) {
		parent::__construct();
		
		$this->sqlOrderBy = 'article.time ' . ARTICLE_SORT_ORDER;
		
		$tagIDs = TagEngine::getInstance()->getTagIDs($tags);
		$this->getConditionBuilder()->add("article.articleID IN (
			SELECT articleID FROM wcf".WCF_N."_article_content WHERE articleContentID IN (
				SELECT          objectID
				FROM            wcf".WCF_N."_tag_to_object
				WHERE           objectTypeID = ?
						AND tagID IN (?)
				GROUP BY        objectID
				HAVING          COUNT(objectID) = ?
			)
		)", [
			TagEngine::getInstance()->getObjectTypeID('com.woltlab.wcf.article'),
			$tagIDs,
			count($tagIDs)
		]);
	}
}
