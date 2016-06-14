<?php
namespace wcf\data\smiley;
use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a smiley.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Smiley
 *
 * @property-read	integer		$smileyID
 * @property-read	integer		$packageID
 * @property-read	integer|null	$categoryID
 * @property-read	string		$smileyPath
 * @property-read	string		$smileyTitle
 * @property-read	string		$smileyCode
 * @property-read	string		$aliases
 * @property-read	integer		$showOrder
 */
class Smiley extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'smiley';
	
	/**
	 * @inheritDoc
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
	 * @return	string[]
	 */
	public function getAliases() {
		if (!$this->aliases) return [];
		
		return explode("\n", StringUtil::unifyNewlines($this->aliases));
	}
}
