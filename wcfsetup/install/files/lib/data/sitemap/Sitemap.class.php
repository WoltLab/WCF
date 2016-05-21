<?php
namespace wcf\data\sitemap;
use wcf\data\DatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\SystemException;
use wcf\system\sitemap\ISitemapProvider;

/**
 * Represents a sitemap entry.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.sitemap
 * @category	Community Framework
 *
 * @property-read	integer		$sitemapID
 * @property-read	integer		$packageID
 * @property-read	string		$sitemapName
 * @property-read	string		$className
 * @property-read	integer		$showOrder
 * @property-read	string		$permissions
 * @property-read	string		$options
 */
class Sitemap extends DatabaseObject {
	use TDatabaseObjectOptions;
	use TDatabaseObjectPermissions;
	
	/**
	 * ISitemapProvider object
	 * @var	\wcf\system\sitemap\ISitemapProvider
	 */
	protected $sitemapObj = null;
	
	/**
	 * database table for this object
	 * @var	string
	 */
	protected static $databaseTableName = 'sitemap';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'sitemapID';
	
	/**
	 * Returns parsed template for current sitemap.
	 * 
	 * @return	string
	 * @throws	SystemException
	 */
	public function getTemplate() {
		if ($this->sitemapObj === null) {
			if (empty($this->className) || !class_exists($this->className)) {
				throw new SystemException("Unable to find class '".$this->className."' for sitemap '".$this->sitemapName."'");
			}
			
			if (!is_subclass_of($this->className, ISitemapProvider::class)) {
				throw new ImplementationException($this->className, ISitemapProvider::class);
			}
			
			$this->sitemapObj = new $this->className();
		}
		
		return $this->sitemapObj->getTemplate();
	}
	
	/**
	 * Returns true, if the active user has access to this sitemap.
	 * 
	 * @return	boolean
	 */
	public function isAccessible() {
		return $this->validateOptions() && $this->validatePermissions();
	}
}
