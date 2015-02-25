<?php
namespace wcf\data\bbcode;
use wcf\data\ProcessibleDatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a bbcode.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.bbcode
 * @category	Community Framework
 */
class BBCode extends ProcessibleDatabaseObject implements IRouteController {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'bbcode';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'bbcodeID';
	
	/**
	 * @see	\wcf\data\ProcessibleDatabaseObject::$processorInterface
	 */
	protected static $processorInterface = 'wcf\system\bbcode\IBBCode';
	
	/**
	 * Returns the attributes of this bbcode.
	 * 
	 * @return	array<\wcf\data\bbcode\attribute\BBCodeAttribute>
	 */
	public function getAttributes() {
		if ($this->attributes === null) {
			$this->data['attributes'] = BBCodeCache::getInstance()->getBBCodeAttributes($this->bbcodeTag);
		}
		
		return $this->attributes;
	}
	
	/**
	 * @see	\wcf\data\ITitledObject::getTitle()
	 */
	public function getTitle() {
		return $this->bbcodeTag;
	}
	
	/**
	 * Returns BBCode object with the given tag.
	 * 
	 * @param	string		$tag
	 * @return	\wcf\data\bbcode\BBCode
	 */
	public static function getBBCodeByTag($tag) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_bbcode
			WHERE	bbcodeTag = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($tag));
		$row = $statement->fetchArray();
		if (!$row) $row = array();
		
		return new self(null, $row);
	}
	
	/**
	 * Returns true if the given BBCode tag is allowed by the given list of
	 * BBCode tags. If the relevant BBCode should be globally disabled or non-
	 * existent, false is returned.
	 * 
	 * @param	string		$bbcodeTag
	 * @param	array<string>	$allowedBBCodeTags
	 * @return	boolean
	 */
	public static function isAllowedBBCode($bbcodeTag, array $allowedBBCodeTags) {
		// check if bbcode is unknown or disabled
		if (BBCodeCache::getInstance()->getBBCodeByTag($bbcodeTag) === null) {
			return false;
		}
		
		// all BBCodes are allowed
		if (in_array('all', $allowedBBCodeTags)) {
			return true;
		}
		
		// no BBCode are allowed
		if (in_array('none', $allowedBBCodeTags)) {
			return false;
		}
		
		return in_array($bbcodeTag, $allowedBBCodeTags);
	}
	
	/**
	 * Returns true if this BBCode can be deleted.
	 * 
	 * @return	boolean
	 */
	public function canDelete() {
		if ($this->originIsSystem) {
			return false;
		}
		
		return true;
	}
}
