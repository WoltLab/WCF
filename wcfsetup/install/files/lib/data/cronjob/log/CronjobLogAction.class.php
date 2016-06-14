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
 * @package	WoltLabSuite\Core\Data\Acp\Menu\Item
 * 
 * @method	CronjobLog		create()
 * @method	CronjobLogEditor[]	getObjects()
 * @method	CronjobLogEditor	getSingleObject()
 */
class CronjobLogAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = CronjobLogEditor::class;
	
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
