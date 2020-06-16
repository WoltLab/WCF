<?php
namespace wcf\system\sitemap\object;
use wcf\data\article\content\ArticleContent;
use wcf\data\DatabaseObject;
use wcf\data\page\PageCache;

/**
 * Article sitemap implementation. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
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
		return $object->getArticle()->canRead();
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAvailableType() {
		return MODULE_ARTICLE && PageCache::getInstance()->getPageByIdentifier('com.woltlab.wcf.Article')->allowSpidersToIndex;
	}
}
