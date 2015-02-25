<?php
namespace wcf\system\application;
use wcf\data\application\ApplicationAction;
use wcf\data\application\ApplicationList;
use wcf\system\cache\builder\ApplicationCacheBuilder;
use wcf\system\Regex;
use wcf\system\SingletonFactory;

/**
 * Handles multi-application environments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
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
		$this->cache = ApplicationCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Returns the primary application.
	 * 
	 * @return	\wcf\data\application\Application
	 */
	public function getPrimaryApplication() {
		$packageID = ($this->cache['primary']) ?: PACKAGE_ID;
		
		if (isset($this->cache['application'][$packageID])) {
			return $this->cache['application'][$packageID];
		}
		
		return $this->cache['wcf'];
	}
	
	/**
	 * Returns an application based upon it's abbreviation. Will return the
	 * primary application if $abbreviation equals to 'wcf'
	 * 
	 * @return	\wcf\data\application\Application
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
	 * Returns pseudo-application representing WCF used for special cases,
	 * e.g. cross-domain files requestable through the webserver.
	 * 
	 * @return	\wcf\data\application\Application
	 */
	public function getWCF() {
		return $this->cache['wcf'];
	}
	
	/**
	 * Returns the currently active application.
	 * 
	 * @return	\wcf\data\application\Application
	 */
	public function getActiveApplication() {
		// work-around during WCFSetup
		if (isset($this->cache['application'][PACKAGE_ID])) {
			return $this->cache['application'][PACKAGE_ID];
		}
		
		return $this->getWCF();
	}
	
	/**
	 * Returns a list of dependent applications.
	 * 
	 * @return	array<\wcf\data\application\Application>
	 */
	public function getDependentApplications() {
		$applications = $this->getApplications();
		foreach ($applications as $key => $application) {
			if ($application->packageID == $this->getActiveApplication()->packageID) {
				unset($applications[$key]);
				break;
			}
		}
		
		return $applications;
	}
	
	/**
	 * Returns a list of all active applications.
	 * 
	 * @return	array<\wcf\data\application\Application>
	 */
	public function getApplications() {
		return $this->cache['application'];
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
	 * Returns true if given $url is an internal URL.
	 * 
	 * @param	string		$url
	 * @return	boolean
	 */
	public function isInternalURL($url) {
		$protocolRegex = new Regex('^https(?=://)');
		if (empty($this->pageURLs)) {
			foreach ($this->getApplications() as $application) {
				$this->pageURLs[] = $protocolRegex->replace($application->getPageURL(), 'http');
			}
		}
		
		foreach ($this->pageURLs as $pageURL) {
			if (stripos($protocolRegex->replace($url, 'http'), $pageURL) === 0) {
				return true;
			}
		}
		
		// relative urls contain no protocol, including implied
		if (!preg_match('~^([a-z]+)?://~', $url)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Rebuilds cookie domain/path for all applications.
	 */
	public static function rebuild() {
		$applicationList = new ApplicationList();
		$applicationList->readObjects();
		
		$applicationAction = new ApplicationAction($applicationList->getObjects(), 'rebuild');
		$applicationAction->executeAction();
	}
}
