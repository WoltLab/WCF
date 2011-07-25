<?php
namespace wcf\page;
use wcf\system\WCF;
use wcf\system\event\EventHandler;

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
	 * Name of the template for the called page.
	 * 
	 * @var string
	 */
	public $templateName = '';
	
	/**
	 * Value of the given action parameter.
	 * 
	 * @var string
	 */
	public $action = '';
	
	/**
	 * Needed modules to view this page.
	 * 
	 * @var	array<string>
	 */
	public $neededModules = array();
	
	/**
	 * Needed permissions to view this page.
	 * 
	 * @var array<string>
	 */
	public $neededPermissions = array();
	
	/**
	 * Creates a new AbstractPage object.
	 * Calls the readParameters() and show() methods automatically.
	 */
	public function __construct() {
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
					throw new wcf\system\exception\IllegalLinkException();
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
		
		// check permission
		if (count($this->neededPermissions)) {
			WCF::getSession()->checkPermission($this->neededPermissions);
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

		// assign variables
		$this->assignVariables();		
		
		// call show event
		EventHandler::getInstance()->fireAction($this, 'show');
		
		// show template
		if (!empty($this->templateName)) {
			WCF::getTPL()->display($this->templateName);
		}
	}
}
