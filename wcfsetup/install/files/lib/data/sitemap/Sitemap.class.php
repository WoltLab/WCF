<?php
namespace wcf\data\sitemap;
use wcf\data\DatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Represents a sitemap entry.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.sitemap
 * @category	Community Framework
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
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'sitemapID';
	
	/**
	 * Returns parsed template for current sitemap.
	 * 
	 * @return	string
	 */
	public function getTemplate() {
		if ($this->sitemapObj === null) {
			if (empty($this->className) || !class_exists($this->className)) {
				throw new SystemException("Unable to find class '".$this->className."' for sitemap '".$this->sitemapName."'");
			}
			
			if (!is_subclass_of($this->className, 'wcf\system\sitemap\ISitemapProvider')) {
				throw new SystemException("'".$this->className."' does not implement 'wcf\system\sitemap\ISitemapProvider'");
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
