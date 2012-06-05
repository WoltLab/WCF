<?php
namespace wcf\action;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * This class provides default implementations for the Action interface.
 * This includes the call of the default event listeners for an action: readParameters and execute.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category 	Community Framework
 */
abstract class AbstractAction implements IAction {
	/**
	 * needed modules to execute this action
	 * @var	array<string>
	 */
	public $neededModules = array();
	
	/**
	 * needed permissions to execute this action
	 * @var array<string>
	 */
	public $neededPermissions = array();
	
	/**
	 * @see	wcf\form\IAction::__run()
	 */
	public final function __construct() { }
	
	/**
	 * @see	wcf\action\IAction::__run()
	 */
	public function __run() {
		// call default methods
		$this->readParameters();
		$this->execute();
	}
	
	/**
	 * @see wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		// call readParameters event
		EventHandler::getInstance()->fireAction($this, 'readParameters');
	}
	
	/**
	 * @see wcf\action\IAction::execute()
	 */
	public function execute() {
		// check modules
		if (count($this->neededModules)) {
			foreach ($this->neededModules as $module) {
				if (!defined($module) || !constant($module)) throw new IllegalLinkException();
			}
		}
		
		// check permission
		if (count($this->neededPermissions)) {
			WCF::getSession()->checkPermissions($this->neededPermissions);
		}
		
		// call execute event
		EventHandler::getInstance()->fireAction($this, 'execute');
	}
	
	/**
	 * Calls the 'executed' event after the successful execution of this action.
	 * This functions won't called automatically. You must do this manually, if you inherit AbstractAction.
	 */
	protected function executed() {
		EventHandler::getInstance()->fireAction($this, 'executed');
	}
}
