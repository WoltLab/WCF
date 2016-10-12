<?php
namespace wcf\data\article\category;
use wcf\data\category\CategoryNode;

/**
 * Represents an article category node.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article\Category
 * @since	3.0
 * 
 * @method	ArticleCategory	getDecoratedObject()
 * @mixin	ArticleCategory
 */
class ArticleCategoryNode extends CategoryNode {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ArticleCategory::class;
}
