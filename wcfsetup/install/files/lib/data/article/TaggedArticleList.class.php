<?php
namespace wcf\data\article;
use wcf\data\tag\Tag;
use wcf\system\tagging\TagEngine;

/**
 * Represents a list of tagged articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article
 * @since	3.0
 */
class TaggedArticleList extends AccessibleArticleList {
	/**
	 * Creates a new CategoryArticleList object.
	 *
	 * @param	Tag	$tag
	 */
	public function __construct(Tag $tag) {
		parent::__construct();
		
		$this->getConditionBuilder()->add("article.articleID IN (SELECT articleID FROM wcf".WCF_N."_article_content WHERE articleContentID IN (SELECT objectID FROM wcf".WCF_N."_tag_to_object WHERE objectTypeID = ? AND languageID = ? AND tagID = ?))", [TagEngine::getInstance()->getObjectTypeID('com.woltlab.wcf.article'), $tag->languageID, $tag->tagID]);
	}
}
