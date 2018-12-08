<?php
namespace wcf\system\page\handler;
use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\IAccessibleObject;

/**
 * Implementation of the `IMenuPageHandler::isVisible()` methods for decorated category-bound pages.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.0
 */
trait TDecoratedCategoryMenuPageHandler {
	/**
	 * Returns the name of the decorated class name.
	 *
	 * @return	string
	 */
	abstract protected function getDecoratedCategoryClass();
	
	/**
	 * Returns false if this page should be hidden from menus, but does not control the accessibility
	 * of the page itself. The visibility can optionally be scoped to the given object id.
	 *
	 * @param	integer|null	$objectID	optional page object id
	 * @return	boolean		false if the page should be hidden from menus
	 * @see	IMenuPageHandler::isVisible()
	 */
	public function isVisible($objectID = null) {
		$className = $this->getDecoratedCategoryClass();
		
		/** @var AbstractDecoratedCategory $category */
		/** @noinspection PhpUndefinedMethodInspection */
		$category = $className::getCategory($objectID);
		
		// check if category exists
		if ($category === null) {
			return false;
		}
		
		// check if access to category is restricted
		if ($category instanceof IAccessibleObject && !$category->isAccessible()) {
			return false;
		}
		
		// fallback to default value of AbstractMenuPageHandler::isVisible()
		return true;
	}
}
