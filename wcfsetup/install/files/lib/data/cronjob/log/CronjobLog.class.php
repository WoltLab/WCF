<?php
namespace wcf\data\cronjob\log;
use wcf\data\DatabaseObject;

/**
 * Represents a croniob log.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.cronjob.log
 * @category	Community Framework
 *
 * @property-read	integer		$cronjobLogID
 * @property-read	integer		$cronjobID
 * @property-read	integer		$execTime
 * @property-read	integer		$success
 * @property-read	string		$error
 */
class CronjobLog extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'cronjob_log';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'cronjobLogID';
}
