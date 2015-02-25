<?php
namespace wcf\data\smiley;
use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a smiley.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.smiley
 * @category	Community Framework
 */
class Smiley extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'smiley';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'smileyID';
	
	/**
	 * Returns the url to this smiley.
	 * 
	 * @return	string
	 */
	public function getURL() {
		return WCF::getPath().$this->smileyPath;
	}
	
	/**
	 * Returns all aliases for this smiley.
	 * 
	 * @return	array<string>
	 */
	public function getAliases() {
		if (!$this->aliases) return array();
		
		return explode("\n", StringUtil::unifyNewlines($this->aliases));
	}
}
