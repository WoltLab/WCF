<?php
namespace wcf\system\request;

/**
 * Represents a page request.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category 	Community Framework
 */
class Request {
	/**
	 * page class name
	 * @var string
	 */
	protected $className = '';
	
	/**
	 * page name
	 * @var string
	 */
	protected $pageName = '';
	
	/**
	 * page type
	 * @var string
	 */
	protected $pageType = '';
	
	/**
	 * true, if this request was executed already.
	 * @var boolean
	 */
	protected $executed = false;
	
	/**
	 * Creates a new request object.
	 * 
	 * @param	string		$className
	 * @param	string		$pageName
	 * @param	string		$pageType
	 */
	public function __construct($className, $pageName, $pageType) {
		$this->className = $className;
		$this->pageName = $pageName;
		$this->pageType = $pageType;
	}
	
	/**
	 * Executes this request.
	 */
	public function execute() {
		if (!$this->executed) {
			$this->executed = true;
			new $this->className();
		}
	}
	
	/**
	 * Returns true, if this request was executed already.
	 * 
	 * @return boolean
	 */
	public function isExecuted() {
		return $this->executed;
	}
	
	/**
	 * Returns the page class name of this request.
	 * 
	 * @return string
	 */
	public function getClassName() {
		return $this->className; 
	}
	
	/**
	 * Returns the page name of this request.
	 * 
	 * @return string
	 */
	public function getPageName() {
		return $this->pageName;
	}
	
	/**
	 * Returns the page type of this request.
	 * 
	 * @return string
	 */
	public function getPageType() {
		return $this->pageType;
	}
}
?>