<?php
declare(strict_types=1);
namespace wcf\system\article\discussion;
use wcf\data\article\Article;

/**
 * Represents a non-existing discussion provider and is used when there is no other
 * type of discussion being available. This provider is always being evaluated last.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Article\Discussion
 * @since       3.2
 */
class VoidArticleDiscussionProvider extends AbstractArticleDiscussionProvider {
	/**
	 * @inheritDoc
	 */
	public function getDiscussionCount(): int {
		return 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDiscussionCountPhrase(): string {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function renderDiscussions(): string {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public static function isResponsible(Article $article): bool {
		return true;
	}
}
