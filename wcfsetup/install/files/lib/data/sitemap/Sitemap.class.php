<?php
namespace wcf\data\sitemap;
use wcf\data\DatabaseObject;
use wcf\system\exception\SystemException;
use wcf\util\ClassUtil;

/**
 * Represents a sitemap entry.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.sitemap
 * @category	Community Framework
 */
class Sitemap extends DatabaseObject {
	/**
	 * ISitemapProvider object
	 * @var	wcf\system\sitemap\ISitemapProvider
	 */
	protected $sitemapObj = null;
	
	/**
	 * database table for this object
	 * @var	string
	 */
	protected static $databaseTableName = 'sitemap';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
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
			
			if (!ClassUtil::isInstanceOf($this->className, 'wcf\system\sitemap\ISitemapProvider')) {
				throw new SystemException("'".$this->className."' does not implement 'wcf\system\sitemap\ISitemapProvider'");
			}
			
			$this->sitemapObj = new $this->className();
		}
		
		return $this->sitemapObj->getTemplate();
	}
}
