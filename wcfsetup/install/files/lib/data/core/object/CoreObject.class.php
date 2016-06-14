<?php
namespace wcf\data\core\object;
use wcf\data\DatabaseObject;

/**
 * Represents a core object.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Core\Object
 * 
 * @property-read	integer		$objectID
 * @property-read	integer		$packageID
 * @property-read	string		$objectName
 */
class CoreObject extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'core_object';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'objectID';
}
