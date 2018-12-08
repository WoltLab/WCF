<?php
namespace wcf\system\label\object\type;
use wcf\data\article\category\ArticleCategoryNode;
use wcf\data\article\category\ArticleCategoryNodeTree;
use wcf\system\cache\builder\ArticleCategoryLabelCacheBuilder;

/**
 * Object type handler for article categories.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Label\Object\Type
 * @since       3.1
 */
class ArticleCategoryLabelObjectTypeHandler extends AbstractLabelObjectTypeHandler {
	/**
	 * category list
	 * @var	\RecursiveIteratorIterator
	 */
	public $categoryList;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$categoryTree = new ArticleCategoryNodeTree('com.woltlab.wcf.article.category');
		$this->categoryList = $categoryTree->getIterator();
		$this->categoryList->setMaxDepth(0);
	}
	
	/**
	 * @inheritDoc
	 */
	public function setObjectTypeID($objectTypeID) {
		parent::setObjectTypeID($objectTypeID);
		
		$this->container = new LabelObjectTypeContainer($this->objectTypeID);
		/** @var ArticleCategoryNode $category */
		foreach ($this->categoryList as $category) {
			$this->container->add(new LabelObjectType($category->getTitle(), $category->categoryID, 0));
			foreach ($category as $subCategory) {
				$this->container->add(new LabelObjectType($subCategory->getTitle(), $subCategory->categoryID, 1));
				foreach ($subCategory as $subSubCategory) {
					$this->container->add(new LabelObjectType($subSubCategory->getTitle(), $subSubCategory->categoryID, 2));
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		ArticleCategoryLabelCacheBuilder::getInstance()->reset();
	}
}
