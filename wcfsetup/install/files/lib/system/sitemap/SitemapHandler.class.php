<?php
namespace wcf\system\sitemap;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Handles sitemap interactions.
 *
 * @author 	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	sitemap.sitemap
 * @category 	Community Framework
 */
class SitemapHandler extends SingletonFactory {
	/**
	 * sitemap cache
	 * @var	array<wcf\data\sitemap\Sitemap>
	 */
	protected $cache = null;
	
	/**
	 * @see \wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$application = ApplicationHandler::getInstance()->getPrimaryApplication();
		$cacheName = 'sitemap-'.$application->packageID;
		
		CacheHandler::getInstance()->addResource(
			$cacheName,
			WCF_DIR.'cache/cache.'.$cacheName.'.php',
			'wcf\system\cache\builder\SitemapCacheBuilder'
		);
		$this->cache = CacheHandler::getInstance()->get($cacheName);
	}
	
	/**
	 * Returns array of tree items or an empty array if only one sitemap is registered.
	 *  
	 * @return	array<wcf\data\sitemap\Sitemap>
	 */
	public function getTree() {
		$tree = array();
		
		if (count($this->cache) > 0) {
			foreach ($this->cache as $sitemap) {
				$tree[] = $sitemap->sitemapName;
			}
		}
		
		return $tree;
	}
	
	/**
	 * Returns sitemap for given sitemap name or falls back to active package id.
	 * 
	 * @param	string		$sitemapName
	 * @return	wcf\data\sitemap\Sitemap
	 */
	public function getSitemap($sitemapName = '') {
		if (empty($sitemapName)) {
			foreach ($this->cache as $sitemap) {
				if ($sitemap->packageID == PACKAGE_ID) {
					$sitemapName = $sitemap->sitemapName;
				}
			}
			
			if (empty($sitemapName)) {
				$sitemap = reset($this->cache);
				$sitemapName = $sitemap->sitemapName;
			}
		}
		
		foreach ($this->cache as $sitemap) {
			if ($sitemap->sitemapName == $sitemapName) {
				return $sitemap->getTemplate();
			}
		}
		
		return null;
	}
	
	/**
	 * Validates sitemap name.
	 * 
	 * @param	string		$sitemapName
	 */
	public function validateSitemapName($sitemapName) {
		if (empty($sitemapName)) {
			throw new SystemException("Empty sitemap name provided");
		}
		
		$isValid = false;
		foreach ($this->cache as $sitemap) {
			if ($sitemap->sitemapName == $sitemapName) {
				$isValid = true;
			}
		}
		
		if (!$isValid) {
			throw new SystemException("Sitemap name '".$sitemapName."' is unknown");
		}
	}
}