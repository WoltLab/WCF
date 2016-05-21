<?php
namespace wcf\system\sitemap;
use wcf\data\sitemap\Sitemap;
use wcf\system\cache\builder\SitemapCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Handles sitemap interactions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	sitemap.sitemap
 * @category	Community Framework
 */
class SitemapHandler extends SingletonFactory {
	/**
	 * sitemap cache
	 * @var	Sitemap[]
	 */
	protected $cache = null;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->cache = SitemapCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Returns array of tree items or an empty array if only one sitemap is registered.
	 * 
	 * @return	Sitemap[]
	 */
	public function getTree() {
		$tree = [];
		
		if (!empty($this->cache)) {
			foreach ($this->cache as $sitemap) {
				if ($sitemap->isAccessible()) $tree[] = $sitemap->sitemapName;
			}
		}
		
		return $tree;
	}
	
	/**
	 * Returns default sitemap name.
	 * 
	 * @return	string
	 */
	public function getDefaultSitemapName() {
		foreach ($this->cache as $sitemap) {
			if ($sitemap->packageID == PACKAGE_ID && $sitemap->isAccessible()) {
				return $sitemap->sitemapName;
			}
		}
		
		foreach ($this->cache as $sitemap) {
			if ($sitemap->isAccessible()) return $sitemap->sitemapName;
		}
		
		return '';
	}
	
	/**
	 * Returns sitemap for given sitemap name.
	 * 
	 * @param	string		$sitemapName
	 * @return	\wcf\data\sitemap\Sitemap
	 */
	public function getSitemap($sitemapName) {
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
	 * @throws	SystemException
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
