<?php
namespace wcf\system\sitemap\object;
use wcf\data\article\category\ArticleCategory;
use wcf\data\category\CategoryList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;
use wcf\data\page\PageCache;

/**
 * Article category sitemap implementation.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Sitemap\Object
 * @since	3.1
 */
class ArticleCategorySitemapObject extends AbstractSitemapObjectObjectType {
	/**
	 * @inheritDoc
	 */
	public function getObjectClass() {
		throw new \LogicException('Unreachable');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectList() {
		$categoryList = new CategoryList();
		$categoryList->decoratorClassName = ArticleCategory::class;
		$categoryList->getConditionBuilder()->add('objectTypeID = ?', [ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.category', ArticleCategory::OBJECT_TYPE_NAME)]);
		
		return $categoryList;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canView(DatabaseObject $object) {
		/** @var $object ArticleCategory */
		return $object->isAccessible();
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAvailableType() {
		return MODULE_ARTICLE && PageCache::getInstance()->getPageByIdentifier('com.woltlab.wcf.CategoryArticleList')->allowSpidersToIndex;
	}
}
