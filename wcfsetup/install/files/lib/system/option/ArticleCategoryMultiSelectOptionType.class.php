<?php
namespace wcf\system\option;
use wcf\data\article\category\ArticleCategoryNodeTree;

/**
 * Option type implementation for selecting multiple article categories.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class ArticleCategoryMultiSelectOptionType extends AbstractCategoryMultiSelectOptionType {
	/**
	 * @inheritDoc
	 */
	public $objectType = 'com.woltlab.wcf.article.category';
	
	/**
	 * @inheritDoc
	 */
	public $nodeTreeClassname = ArticleCategoryNodeTree::class;
}
