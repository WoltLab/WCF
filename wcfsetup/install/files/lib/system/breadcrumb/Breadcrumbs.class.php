<?php
namespace wcf\system\breadcrumb;
use wcf\system\SingletonFactory;

/**
 * Manages breadcrumbs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.breadcrumb
 * @category 	Community Framework
 */
class Breadcrumbs extends SingletonFactory {
	/**
	 * list of breadcrumbs
	 * 
	 * @var	array<Breadcrumb>
	 */	
	protected $items = array();
	
	/**
	 * Adds a breadcrumb (insert order is crucial!)
	 * 
	 * @param	Breadcrumb		$item
	 */	
	public function add(Breadcrumb $item) {
		$this->items[] = $item;
	}
	
	/**
	 * Returns the list of breadcrumbs.
	 * 
	 * @return	array<Breadcrumb>
	 */	 
	public function get() {
		return $this->items;
	}
	
	/**
	 * Replaces a breadcrumb, returns true if replacement was successful.
	 * 
	 * @param	Breadcrumb		$item
	 * @param	integer			$index
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
	 * @param	integer			$index
	 * @return	boolean
	 */
	public function remove($index) {
		if (isset($this->items[$index])) {
			unset($this->items[$index]);
			
			return true;
		}
		
		return false;
	}
}
