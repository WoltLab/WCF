<?php
namespace wcf\data\cronjob\log;
use wcf\data\DatabaseObject;

/**
 * Represents a croniob log.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Cronjob\Log
 *
 * @property-read	integer		$cronjobLogID
 * @property-read	integer		$cronjobID
 * @property-read	integer		$execTime
 * @property-read	integer		$success
 * @property-read	string		$error
 */
class CronjobLog extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'cronjob_log';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'cronjobLogID';
}
