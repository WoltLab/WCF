<?php
namespace wcf\page;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * This class provides default implementations for the Page interface.
 * This includes the call of the default event listeners for a page: readParameters, readData, assignVariables and show.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category 	Community Framework
 */
abstract class AbstractPage implements IPage {
	/**
	 * name of the template for the called page
	 * @var string
	 */
	public $templateName = '';
	
	/**
	 * enables template usage
	 * @var	string
	 */
	public $useTemplate = true;
	
	/**
	 * value of the given action parameter
	 * @var string
	 */
	public $action = '';
	
	/**
	 * needed modules to view this page
	 * @var	array<string>
	 */
	public $neededModules = array();
	
	/**
	 * needed permissions to view this page
	 * @var array<string>
	 */
	public $neededPermissions = array();
	
	/**
	 * @see	wcf\page\IPage::__run()
	 */
	public function __run() {
		// call default methods
		$this->readParameters();
		$this->show();
	}
	
	/**
	 * @see wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		// call readParameters event
		EventHandler::getInstance()->fireAction($this, 'readParameters');
		
		// read action parameter
		if (isset($_REQUEST['action'])) $this->action = $_REQUEST['action'];
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		// call readData event
		EventHandler::getInstance()->fireAction($this, 'readData');
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		// call assignVariables event
		EventHandler::getInstance()->fireAction($this, 'assignVariables');
		
		// assign parameters
		WCF::getTPL()->assign(array(
			'action' => $this->action,
			'templateName' => $this->templateName
		));
	}
	
	/**
	 * @see wcf\page\IPage::checkModules()
	 */
	public function checkModules() {
		// call checkModules event
		EventHandler::getInstance()->fireAction($this, 'checkModules');
		
		// check modules
		if (count($this->neededModules)) {
			foreach ($this->neededModules as $module) {
				if (!defined($module) || !constant($module)) {
					throw new IllegalLinkException();
				}
			}
		}
	}
	
	/**
	 * @see wcf\page\IPage::checkPermissions()
	 */
	public function checkPermissions() {
		// call checkPermissions event
		EventHandler::getInstance()->fireAction($this, 'checkPermissions');
		
		// check permission, it is sufficient to have at least one permission
		if (!empty($this->neededPermissions)) {
			$hasPermissions = false;
			foreach ($this->neededPermissions as $permission) {
				if (WCF::getSession()->getPermission($permission)) {
					$hasPermissions = true;
					break;
				}
			}
			
			if (!$hasPermissions) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// check modules
		$this->checkModules();
		
		// check permission
		$this->checkPermissions();
		
		// read data
		$this->readData();

		// try to guess template name
		if (empty($this->templateName)) {
			$classParts = explode('\\', get_class($this));
			$className = preg_replace('~(Form|Page)$~', '', array_pop($classParts));
			$this->templateName = lcfirst($className);
		}
		
		// assign variables
		$this->assignVariables();		
		
		// call show event
		EventHandler::getInstance()->fireAction($this, 'show');
		
		if ($this->useTemplate) {
			// show template
			WCF::getTPL()->display($this->templateName);
		}
	}
}
