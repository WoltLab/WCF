<?php
namespace wcf\action;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * This class provides default implementations for the Action interface.
 * This includes the call of the default event listeners for an action: readParameters and execute.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 */
abstract class AbstractAction implements IAction {
	/**
	 * indicates if you need to be logged in to execute this action
	 * @var	boolean
	 */
	public $loginRequired = false;
	
	/**
	 * needed modules to execute this action
	 * @var	string[]
	 */
	public $neededModules = [];
	
	/**
	 * needed permissions to execute this action
	 * @var	string[]
	 */
	public $neededPermissions = [];
	
	/**
	 * @inheritDoc
	 */
	public final function __construct() { }
	
	/**
	 * @inheritDoc
	 */
	public function __run() {
		// call default methods
		$this->readParameters();
		$this->execute();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		// call readParameters event
		EventHandler::getInstance()->fireAction($this, 'readParameters');
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkModules() {
		// call checkModules event
		EventHandler::getInstance()->fireAction($this, 'checkModules');
		
		// check modules
		foreach ($this->neededModules as $module) {
			if (!defined($module) || !constant($module)) {
				throw new IllegalLinkException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions() {
		// call checkPermissions event
		EventHandler::getInstance()->fireAction($this, 'checkPermissions');
		
		// check permission
		if (!empty($this->neededPermissions)) {
			WCF::getSession()->checkPermissions($this->neededPermissions);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		// check if active user is logged in
		if ($this->loginRequired && !WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		// check modules
		$this->checkModules();
		
		// check permissions
		$this->checkPermissions();
		
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
