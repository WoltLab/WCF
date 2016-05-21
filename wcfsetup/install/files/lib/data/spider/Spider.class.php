<?php
namespace wcf\data\spider;
use wcf\data\DatabaseObject;

/**
 * Represents a spider.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.spider
 * @category	Community Framework
 *
 * @property-read	integer		$spiderID
 * @property-read	string		$spiderIdentifier
 * @property-read	string		$spiderName
 * @property-read	string		$spiderURL
 */
class Spider extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'spider';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'spiderID';
}
