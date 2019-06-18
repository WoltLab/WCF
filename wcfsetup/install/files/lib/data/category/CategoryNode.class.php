<?php
namespace wcf\data\category;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\IObjectTreeNode;
use wcf\data\TObjectTreeNode;

/**
 * Represents a category node.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Category
 * 
 * @method	Category	getDecoratedObject()
 * @mixin	Category
 */
class CategoryNode extends DatabaseObjectDecorator implements IObjectTreeNode {
	use TObjectTreeNode;
	
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Category::class;
	
	/**
	 * Returns true if this category is visible in a nested menu item list.
	 *
	 * @param       AbstractDecoratedCategory        $activeCategory
	 * @return	boolean
	 * @since       5.2
	 */
	public function isVisibleInNestedList(AbstractDecoratedCategory $activeCategory = null) {
		if (!$this->getParentCategory() || !$this->getParentCategory()->getParentCategory()) {
			// level 1 & 2 are always visible
			return true;
		}
		
		if ($activeCategory) {
			if ($activeCategory->categoryID == $this->categoryID || $activeCategory->isParentCategory($this->getDecoratedObject())) {
				// is the active category or a parent of the active category
				return true;
			}
			
			if ($this->getParentCategory()->categoryID == $activeCategory->categoryID) {
				// is a direct child element of the active category
				return true;
			}
		}
		
		return false;
	}
}
