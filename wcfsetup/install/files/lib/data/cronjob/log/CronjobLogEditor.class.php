<?php
namespace wcf\data\cronjob\log;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit cronjob logs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language.item
 * @category	Community Framework
 */
class CronjobLogEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\cronjob\log\CronjobLog';
	
	/**
	 * Deletes all cronjob logs.
	 */
	public static function clearLogs() {
		// delete logs
		$sql = "DELETE FROM	wcf".WCF_N."_cronjob_log";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
	}
}
