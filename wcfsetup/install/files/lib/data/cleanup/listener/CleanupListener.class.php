<?php
namespace wcf\data\cleanup\listener;
use wcf\data\DatabaseObject;

/**
 * Represents a cleanup listener.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.cleanup.listener
 * @category	Community Framework
 */
class CleanupListener extends DatabaseObject {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'cleanup_listener';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'listenerID';
}
