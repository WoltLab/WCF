<?php
namespace wcf\system\breadcrumb;
use wcf\system\menu\page\PageMenu;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages breadcrumbs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.breadcrumb
 * @category	Community Framework
 */
class Breadcrumbs extends SingletonFactory implements \Countable, \Iterator {
	/**
	 * list of breadcrumbs
	 * @var	array<\wcf\system\breadcrumb\Breadcrumb>
	 */
	protected $items = array();
	
	/**
	 * Current iterator-index
	 */
	protected $index = 0;
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		// add main breadcrumbs entry
		$this->add(new Breadcrumb(WCF::getLanguage()->get(PAGE_TITLE), PageMenu::getInstance()->getLandingPage()->getProcessor()->getLink()));
	}
	
	/**
	 * Adds a breadcrumb (insertion order is crucial!).
	 * 
	 * @param	\wcf\system\breadcrumb\Breadcrumb	$item
	 */
	public function add(Breadcrumb $item) {
		$this->items[] = $item;
	}
	
	/**
	 * Returns the list of breadcrumbs.
	 * 
	 * @return	array<\wcf\system\breadcrumb\Breadcrumb>
	 */
	public function get() {
		return $this->items;
	}
	
	/**
	 * Replaces a breadcrumb, returns true if replacement was successful.
	 * 
	 * @param	\wcf\system\breadcrumb\Breadcrumb	$item
	 * @param	integer					$index
	 * @return	boolean
	 */
	public function replace(Breadcrumb $item, $index) {
		if (isset($this->items[$index])) {
			$this->items[$index] = $item;
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Removes a breadcrumb, returns true if deletion was successful.
	 * 
	 * @param	integer		$index
	 * @return	boolean
	 */
	public function remove($index) {
		if (isset($this->items[$index])) {
			unset($this->items[$index]);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * @see	\Countable::count()
	 */
	public function count() {
		return count($this->items);
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		return $this->items[$this->index];
	}
	
	/**
	 * @see	\Iterator::key()
	 */
	public function key() {
		return $this->index;
	}
	
	/**
	 * @see	\Iterator::valid()
	 */
	public function valid() {
		return isset($this->items[$this->index]);
	}
	
	/**
	 * @see	\Iterator::rewind()
	 */
	public function rewind() {
		$this->index = 0;
	}
	
	/**
	 * @see	\Iterator::next()
	 */
	public function next() {
		$this->index++;
	}
}
