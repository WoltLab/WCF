<?php
namespace wcf\system\breadcrumb;
use wcf\data\page\PageCache;
use wcf\system\page\PageLocationManager;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages breadcrumbs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Breadcrumb
 */
class Breadcrumbs extends SingletonFactory implements \Countable, \Iterator {
	/**
	 * list of breadcrumbs
	 * @var	Breadcrumb[]
	 */
	protected $items = null;
	
	/**
	 * Current iterator-index
	 */
	protected $index = 0;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {}
	
	/**
	 * Adds a breadcrumb (insertion order is crucial!).
	 * 
	 * @param	Breadcrumb	$item
	 * @deprecated  3.0
	 */
	public function add(Breadcrumb $item) {
		throw new \BadMethodCallException("Breadcrumbs::add() is no longer supported, please use " . PageLocationManager::class . " instead.");
	}
	
	/**
	 * Returns the list of breadcrumbs.
	 * 
	 * @return	Breadcrumb[]
	 */
	public function get() {
		if ($this->items === null) {
			$this->loadBreadcrumbs();
		}
		
		return $this->items;
	}
	
	/**
	 * Replaces a breadcrumb, returns true if replacement was successful.
	 * 
	 * @param	Breadcrumb	$item
	 * @param	integer		$index
	 * @return	boolean
	 * @deprecated  3.0
	 */
	public function replace(Breadcrumb $item, $index) {
		throw new \BadMethodCallException("Breadcrumbs::replace() is no longer supported, please use " . PageLocationManager::class . " instead.");
	}
	
	/**
	 * Removes a breadcrumb, returns true if deletion was successful.
	 * 
	 * @param	integer		$index
	 * @return	boolean
	 * @deprecated  3.0
	 */
	public function remove($index) {
		throw new \BadMethodCallException("Breadcrumbs::remove() is no longer supported, please use " . PageLocationManager::class . " instead.");
	}
	
	protected function loadBreadcrumbs() {
		$this->items = [];
		$locations = PageLocationManager::getInstance()->getLocations();
		
		// locations are provided starting with the current location followed
		// by all relevant ancestors, but breadcrumbs appear in the reverse order
		$locations = array_reverse($locations);
		
		// add the landing page as first location, unless it is already included
		$landingPage = PageCache::getInstance()->getLandingPage();
		$addLandingPage = true;
		for ($i = 0, $length = count($locations); $i < $length; $i++) {
			if ($locations[$i]['pageID'] == $landingPage->pageID) {
				$addLandingPage = false;
				break;
			}
		}
		
		if ($addLandingPage) {
			array_unshift($locations, [
				'link' => $landingPage->getLink(),
				'title' => WCF::getLanguage()->get(PAGE_TITLE)
			]);
		}
		
		// ignore the last location as it represents the current page
		array_pop($locations);
		
		for ($i = 0, $length = count($locations); $i < $length; $i++) {
			$this->items[] = new Breadcrumb($locations[$i]['title'], $locations[$i]['link']);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function count() {
		if ($this->items === null) {
			$this->loadBreadcrumbs();
		}
		
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
