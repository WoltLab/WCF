<?php
namespace wcf\data\cronjob\log;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes cronjob log-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.menu.item
 * @category 	Community Framework
 */
class CronjobLogAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\cronjob\log\CronjobLogEditor';
}
