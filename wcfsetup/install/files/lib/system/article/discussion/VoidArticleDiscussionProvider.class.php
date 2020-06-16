<?php
namespace wcf\system\article\discussion;
use wcf\data\article\Article;

/**
 * Represents a non-existing discussion provider and is used when there is no other
 * type of discussion being available. This provider is always being evaluated last.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Article\Discussion
 * @since       5.2
 */
class VoidArticleDiscussionProvider extends AbstractArticleDiscussionProvider {
	/**
	 * @inheritDoc
	 */
	public function getDiscussionCount() {
		return 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDiscussionCountPhrase() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDiscussionLink() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function renderDiscussions() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public static function isResponsible(Article $article) {
		return true;
	}
}
