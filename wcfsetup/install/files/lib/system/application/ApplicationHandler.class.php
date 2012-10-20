<?php
namespace wcf\system\application;
use wcf\system\cache\CacheHandler;
use wcf\system\SingletonFactory;
use wcf\util\StringUtil;

/**
 * Handles multi-application environments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.application
 * @category	Community Framework
 */
class ApplicationHandler extends SingletonFactory {
	/**
	 * application cache
	 * @var	array<array>
	 */	
	protected $cache = null;
	
	/**
	 * list of page URLs
	 * @var	array<string>
	 */
	protected $pageURLs = array();
	
	/**
	 * Initializes cache.
	 */
	protected function init() {
		$cacheName = 'application-'.PACKAGE_ID;
		CacheHandler::getInstance()->addResource(
			$cacheName,
			WCF_DIR.'cache/cache.'.$cacheName.'.php',
			'wcf\system\cache\builder\ApplicationCacheBuilder'
		);
		$this->cache = CacheHandler::getInstance()->get($cacheName);
	}
	
	/**
	 * Returns the primary application for current group. Will return current
	 * application equal to PACKAGE_ID if not within any group.
	 * 
	 * @return	wcf\data\application\Application
	 */
	public function getPrimaryApplication() {
		$packageID = ($this->cache['primary']) ?: PACKAGE_ID;
		return $this->cache['application'][$packageID];
	}
	
	/**
	 * Returns an application based upon it's abbreviation. Will return the
	 * primary application if $abbreviation equals to 'wcf'
	 * 
	 * @return	wcf\data\application\Application
	 */	 
	public function getApplication($abbreviation) {
		if ($abbreviation == 'wcf') {
			return $this->getPrimaryApplication();
		}
		
		if (isset($this->cache['abbreviation'][$abbreviation])) {
			$packageID = $this->cache['abbreviation'][$abbreviation];
			
			if (isset($this->cache['application'][$packageID])) {
				return $this->cache['application'][$packageID];
			}
		}
		
		return null;
	}
	
	/**
	 * Returns active application group or 'null' if current application
	 * is not within a group.
	 * 
	 * @return	wcf\data\application\group\ApplicationGroup
	 */	
	public function getActiveGroup() {
		return $this->cache['group'];
	}
	
	/**
	 * Returns pseudo-application representing WCF used for special cases,
	 * e.g. cross-domain files requestable through the webserver.
	 * 
	 * @return	wcf\data\application\Application
	 */
	public function getWCF() {
		return $this->cache['wcf'];
	}
	
	/**
	 * Returns the currently active application.
	 * 
	 * @return	wcf\data\application\Application
	 */	
	public function getActiveApplication() {
		return $this->cache['application'][PACKAGE_ID];
	}
	
	/**
	 * Returns a list of dependent applications.
	 * 
	 * @return	array<wcf\data\application\Application>
	 */	
	public function getDependentApplications() {
		$applications = array();
		foreach ($this->cache['application'] as $packageID => $application) {
			if ($packageID == PACKAGE_ID) continue;
			
			$applications[] = $application;
		}
		
		return $applications;
	}
	
	/**
	 * Returns a list of all active applications.
	 * 
	 * @return	array<wcf\data\application\Application>
	 */
	public function getApplications() {
		$applications = $this->getDependentApplications();
		$applications[] = $this->getActiveApplication();
		
		return $applications;
	}
	
	/**
	 * Returns abbreviation for a given package id or null if application is unknown.
	 * 
	 * @return	string
	 */
	public function getAbbreviation($packageID) {
		foreach ($this->cache['abbreviation'] as $abbreviation => $applicationID) {
			if ($packageID == $applicationID) {
				return $abbreviation;
			}
		}
		
		return null;
	}
	
	/**
	 * Returns true, if given $url is an internal URL.
	 * 
	 * @param	string		$url
	 * @return	boolean
	 */
	public function isInternalURL($url) {
		if (empty($this->pageURLs)) {
			foreach ($this->getApplications() as $application) {
				$this->pageURLs[] = $application->getPageURL();
			}
			
			if (defined('PAGE_URLS') && PAGE_URLS != '') {
				$pageURLs = explode("\n", StringUtil::unifyNewlines(PAGE_URLS));
				foreach ($pageURLs as $url) {
					$this->pageURLs[] = StringUtil::trim($url);
				}
			}
		}
		
		foreach ($this->pageURLs as $pageURL) {
			if (stripos($url, $pageURL) === 0) {
				return true;
			}
		}
		
		return false;
	}
}
