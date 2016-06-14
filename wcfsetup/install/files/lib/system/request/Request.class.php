<?php
namespace wcf\system\request;
use wcf\data\page\PageCache;

/**
 * Represents a page request.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Request
 */
class Request {
	/**
	 * page class name
	 * @var	string
	 */
	protected $className = '';
	
	/**
	 * @var boolean
	 */
	protected $isLandingPage = false;
	
	/**
	 * request meta data
	 * @var	string[]
	 */
	protected $metaData;
	
	/**
	 * current page id
	 * @var integer
	 */
	protected $pageID;
	
	/**
	 * page name
	 * @var	string
	 */
	protected $pageName = '';
	
	/**
	 * page type
	 * @var	string
	 */
	protected $pageType = '';
	
	/**
	 * request object
	 * @var	object
	 */
	protected $requestObject;
	
	/**
	 * Creates a new request object.
	 * 
	 * @param	string		$className	fully qualified name
	 * @param	string		$pageName	class name
	 * @param	string		$pageType	can be 'action', 'form' or 'page'
	 * @param	string[]	$metaData	additional meta data
	 */
	public function __construct($className, $pageName, $pageType, array $metaData) {
		$this->className = $className;
		$this->metaData = $metaData;
		$this->pageName = $pageName;
		$this->pageType = $pageType;
	}
	
	/**
	 * Marks this request as landing page.
	 */
	public function setIsLandingPage() {
		$this->isLandingPage = true;
	}
	
	/**
	 * Executes this request.
	 */
	public function execute() {
		if ($this->requestObject === null) {
			$this->requestObject = new $this->className();
			$this->requestObject->__run();
		}
	}
	
	/**
	 * Returns true if this request has already been executed.
	 * 
	 * @return	boolean
	 */
	public function isExecuted() {
		return ($this->requestObject !== null);
	}
	
	/**
	 * Returns true if this request represents the landing page.
	 * 
	 * @return boolean
	 */
	public function isLandingPage() {
		return $this->isLandingPage;
	}
	
	/**
	 * Returns the page class name of this request.
	 * 
	 * @return	string
	 */
	public function getClassName() {
		return $this->className;
	}
	
	/**
	 * Returns request meta data.
	 * 
	 * @return	string[]
	 * @since	3.0
	 */
	public function getMetaData() {
		return $this->metaData;
	}
	
	/**
	 * Returns the page name of this request.
	 * 
	 * @return	string
	 */
	public function getPageName() {
		return $this->pageName;
	}
	
	/**
	 * Returns the page type of this request.
	 * 
	 * @return	string
	 */
	public function getPageType() {
		return $this->pageType;
	}
	
	/**
	 * Returns the current request object.
	 * 
	 * @return	object
	 */
	public function getRequestObject() {
		return $this->requestObject;
	}
	
	/**
	 * Returns true if the requested page is avaiable during the offline mode.
	 * 
	 * @return	boolean
	 */
	public function isAvailableDuringOfflineMode() {
		if (defined($this->className . '::AVAILABLE_DURING_OFFLINE_MODE') && constant($this->className . '::AVAILABLE_DURING_OFFLINE_MODE')) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the current page id.
	 * 
	 * @return	integer		current page id or `0` if unknown
	 */
	public function getPageID() {
		if ($this->pageID === null) {
			if (isset($this->metaData['cms'])) {
				$this->pageID = $this->metaData['cms']['pageID'];
			}
			else {
				$page = PageCache::getInstance()->getPageByController($this->className);
				if ($page !== null) {
					$this->pageID = $page->pageID;
				}
				else {
					$this->pageID = 0;
				}
			}
		}
		
		return $this->pageID;
	}
}
