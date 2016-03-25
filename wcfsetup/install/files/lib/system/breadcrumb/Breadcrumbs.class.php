<?php
namespace wcf\system\breadcrumb;
use wcf\system\SingletonFactory;

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
	 * @var	Breadcrumb[]
	 */
	protected $items = [];
	
	/**
	 * Current iterator-index
	 */
	protected $index = 0;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		// add main breadcrumbs entry
		// TODO: there is no longer a global landing page, what should be displayed instead?
		//$this->add(new Breadcrumb(WCF::getLanguage()->get(PAGE_TITLE), PageMenu::getInstance()->getLandingPage()->getProcessor()->getLink()));
	}
	
	/**
	 * Adds a breadcrumb (insertion order is crucial!).
	 * 
	 * @param	Breadcrumb	$item
	 */
	public function add(Breadcrumb $item) {
		$this->items[] = $item;
	}
	
	/**
	 * Returns the list of breadcrumbs.
	 * 
	 * @return	Breadcrumb[]
	 */
	public function get() {
		return $this->items;
	}
	
	/**
	 * Replaces a breadcrumb, returns true if replacement was successful.
	 * 
	 * @param	Breadcrumb	$item
	 * @param	integer		$index
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
	 * @inheritDoc
	 */
	public function count() {
		return count($this->items);
	}
	
	/**
	 * @inheritDoc
	 */
	public function current() {
		return $this->items[$this->index];
	}
	
	/**
	 * @inheritDoc
	 */
	public function key() {
		return $this->index;
	}
	
	/**
	 * @inheritDoc
	 */
	public function valid() {
		return isset($this->items[$this->index]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function rewind() {
		$this->index = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function next() {
		$this->index++;
	}
}
