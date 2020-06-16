<?php
namespace wcf\data\cronjob\log;
use wcf\data\DatabaseObject;

/**
 * Represents a cronjob execution log.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Cronjob\Log
 *
 * @property-read	integer		$cronjobLogID	unique id of the cronjob execution log
 * @property-read	integer		$cronjobID	id of the cronjob the log belongs to
 * @property-read	integer		$execTime	timestamp at which the cronjob has been executed
 * @property-read	integer		$success	is `1` if the cronjob has been successfully executed, otherwise `0`
 * @property-read	string		$error		error message if the cronjob did not execute successfully, otherwise empty
 */
class CronjobLog extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'cronjobLogID';
}
