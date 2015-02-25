<?php
namespace wcf\data\page\menu\item;
use wcf\data\DatabaseObjectDecorator;

/**
 * Provides a viewable page menu item with children support.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page.menu.item
 * @category	Community Framework
 */
class ViewablePageMenuItem extends DatabaseObjectDecorator implements \Countable, \Iterator {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\page\menu\item\PageMenuItem';
	
	/**
	 * current iterator index
	 * @var	integer
	 */
	protected $index = 0;
	
	/**
	 * list of page menu items
	 * @var	array<\wcf\data\page\menu\item\PageMenuItem>
	 */
	protected $objects = array();
	
	/**
	 * Adds a page menu item to collection.
	 * 
	 * @param	\wcf\data\page\menu\item\PageMenuItem	$menuItem
	 */
	public function addChild(PageMenuItem $menuItem) {
		if ($menuItem->parentMenuItem == $this->menuItem) {
			$this->objects[] = $menuItem;
		}
	}
	
	/**
	 * @see	\Countable::count()
	 */
	public function count() {
		return count($this->objects);
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		return $this->objects[$this->index];
	}
	
	/**
	 * @see	\Iterator::key()
	 */
	public function key() {
		return $this->index;
	}
	
	/**
	 * @see	\Iterator::next()
	 */
	public function next() {
		++$this->index;
	}
	
	/**
	 * @see	\Iterator::rewind()
	 */
	public function rewind() {
		$this->index = 0;
	}
	
	/**
	 * @see	\Iterator::valid()
	 */
	public function valid() {
		return isset($this->objects[$this->index]);
	}
}
