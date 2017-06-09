<?php
namespace wcf\system\sitemap\object;
use wcf\data\article\content\ArticleContent;
use wcf\data\article\Article;
use wcf\data\DatabaseObject;

/**
 * Article sitemap implementation. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Sitemap\Object
 * @since	3.1
 */
class ArticleSitemapObject extends AbstractSitemapObjectObjectType {
	/**
	 * @inheritDoc
	 */
	public function getObjectClass() {
		return ArticleContent::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canView(DatabaseObject $object) {
		/** @var $object ArticleContent */
		if ($object->getArticle()->publicationStatus != Article::PUBLISHED) {
			return false; 
		}
		
		if ($object->getArticle()->getCategory()) {
			return $object->getArticle()->getCategory()->isAccessible(self::getGuestUserProfile()->getDecoratedObject());
		}
		
		return self::getGuestUserProfile()->getPermission('user.article.canRead');
	}
}
