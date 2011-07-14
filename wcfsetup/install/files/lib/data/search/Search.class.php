<?php
namespace wcf\data\search;
use wcf\data\DatabaseObject;

/**
 * Represents a search.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.search
 * @category 	Community Framework
 */
class Search extends DatabaseObject {
	/**
	 * @see	DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'search';
	
	/**
	 * @see	DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'searchID';	
}
?>