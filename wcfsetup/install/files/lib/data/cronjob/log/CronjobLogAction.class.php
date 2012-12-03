<?php
namespace wcf\data\cronjob\log;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;

/**
 * Executes cronjob log-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.menu.item
 * @category	Community Framework
 */
class CronjobLogAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\cronjob\log\CronjobLogEditor';
	
	/**
	 * Validates the clear all action.
	 */
	public function validateClearAll() {
		WCF::getSession()->checkPermissions(array('admin.system.canManageCronjob'));
	}

	/**
	 * Deletes the entire cronjob log.
	 */
	public function clearAll() {
		CronjobLogEditor::clearLogs();
	}
}
