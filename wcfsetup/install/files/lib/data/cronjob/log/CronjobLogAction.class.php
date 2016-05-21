<?php
namespace wcf\data\cronjob\log;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;

/**
 * Executes cronjob log-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.menu.item
 * @category	Community Framework
 */
class CronjobLogAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = 'wcf\data\cronjob\log\CronjobLogEditor';
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['clearAll'];
	
	/**
	 * Validates the clear all action.
	 */
	public function validateClearAll() {
		WCF::getSession()->checkPermissions(['admin.management.canManageCronjob']);
	}
	
	/**
	 * Deletes the entire cronjob log.
	 */
	public function clearAll() {
		CronjobLogEditor::clearLogs();
	}
}
