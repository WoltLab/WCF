<?php
namespace wcf\system\tagging;
use wcf\data\article\TaggedArticleList;
use wcf\data\tag\Tag;

/**
 * Implementation of ITaggable for tagging of cms articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Tagging
 * @since	3.0
 */
class TaggableArticle extends AbstractTaggable {
	/**
	 * @inheritDoc
	 */
	public function getObjectList(Tag $tag) {
		return new TaggedArticleList($tag);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTemplateName() {
		return 'articleListItems';
	}
}
