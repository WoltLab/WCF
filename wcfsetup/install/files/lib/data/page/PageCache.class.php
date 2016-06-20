<?php
namespace wcf\data\page;
use wcf\system\cache\builder\PageCacheBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Provides access to the page cache.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Page
 * @since	3.0
 */
class PageCache extends SingletonFactory {
	/**
	 * page cache
	 * @var	array
	 */
	protected $cache;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->cache = PageCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Returns all available pages.
	 * 
	 * @return	Page[]
	 */
	public function getPages() {
		return $this->cache['pages'];
	}
	
	/**
	 * Returns a page by page id or null.
	 * 
	 * @param	integer		$pageID		page id
	 * @return	Page|null
	 */
	public function getPage($pageID) {
		if (isset($this->cache['pages'][$pageID])) {
			return $this->cache['pages'][$pageID];
		}
		
		return null;
	}
	
	/**
	 * Returns a page by controller or null.
	 * 
	 * @param	string		$controller	controller class name
	 * @return	Page|null
	 */
	public function getPageByController($controller) {
		if (isset($this->cache['controller'][$controller])) {
			return $this->getPage($this->cache['controller'][$controller]);
		}
		
		return null;
	}
	
	/**
	 * Returns a page by its internal identifier or null.
	 * 
	 * @param	string		$identifier	internal identifier
	 * @return	Page|null
	 */
	public function getPageByIdentifier($identifier) {
		if (isset($this->cache['identifier'][$identifier])) {
			return $this->getPage($this->cache['identifier'][$identifier]);
		}
		
		return null;
	}
	
	/**
	 * Returns the localized page title by page id, optionally retrieving the title
	 * for given language id if it is a multilingual page.
	 * 
	 * @param	integer		$pageID		page id
	 * @param	integer		$languageID	specific value by language id
	 * @return	string	localized page title
	 */
	public function getPageTitle($pageID, $languageID = null) {
		if (isset($this->cache['pageTitles'][$pageID])) {
			$page = $this->getPage($pageID);
			if ($page->isMultilingual || $page->pageType == 'system') {
				if ($languageID !== null && isset($this->cache['pageTitles'][$pageID][$languageID])) {
					return $this->cache['pageTitles'][$pageID][$languageID];
				}
				
				return $this->cache['pageTitles'][$pageID][WCF::getLanguage()->languageID];
			}
			else {
				return $this->cache['pageTitles'][$pageID][0];
			}
		}
		
		return '';
	}
	
	/**
	 * Returns the global landing page.
	 * 
	 * @return	Page
	 */
	public function getLandingPage() {
		return $this->cache['landingPage'];
	}
}
