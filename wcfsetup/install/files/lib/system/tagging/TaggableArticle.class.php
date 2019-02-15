<?php
namespace wcf\system\tagging;
use wcf\data\article\TaggedArticleList;

/**
 * Implementation of ITaggable for tagging of cms articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Tagging
 * @since	3.0
 */
class TaggableArticle extends AbstractCombinedTaggable {
	/**
	 * @inheritDoc
	 */
	public function getObjectListFor(array $tags) {
		return new TaggedArticleList($tags);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTemplateName() {
		return 'taggedArticleList';
	}
}
