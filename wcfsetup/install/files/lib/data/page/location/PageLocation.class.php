<?php
namespace wcf\data\page\location;
use wcf\data\ProcessibleDatabaseObject;

/**
 * Represents a page location.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page.location
 * @category 	Community Framework
 */
class PageLocation extends ProcessibleDatabaseObject {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'page_location';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseIndexName
	 */
	protected static $databaseIndexName = 'locationID';
	
	/**
	 * @see	wcf\data\ProcessibleDatabaseObject::$processorInterface
	 */
	protected static $processorInterface = 'wcf\data\page\location\ILocation';
}
