<?php
namespace wcf\data\bbcode;
use wcf\data\bbcode\attribute\BBCodeAttribute;
use wcf\data\ProcessibleDatabaseObject;
use wcf\system\bbcode\IBBCode;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a bbcode.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Bbcode
 * 
 * @property-read	integer		$bbcodeID
 * @property-read	string		$bbcodeTag
 * @property-read	integer		$packageID
 * @property-read	string		$htmlOpen
 * @property-read	string		$htmlClose
 * @property-read	string		$className
 * @property-read	integer		$isBlockElement
 * @property-read	string		$wysiwygIcon
 * @property-read	string		$buttonLabel
 * @property-read	integer		$isSourceCode
 * @property-read	integer		$isDisabled
 * @property-read	integer		$showButton
 * @property-read	integer		$originIsSystem
 */
class BBCode extends ProcessibleDatabaseObject implements IRouteController {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'bbcode';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'bbcodeID';
	
	/**
	 * @inheritDoc
	 */
	protected static $processorInterface = IBBCode::class;
	
	/**
	 * Returns the attributes of this bbcode.
	 * 
	 * @return	BBCodeAttribute[]
	 */
	public function getAttributes() {
		if ($this->attributes === null) {
			$this->data['attributes'] = BBCodeCache::getInstance()->getBBCodeAttributes($this->bbcodeTag);
		}
		
		return $this->attributes;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->bbcodeTag;
	}
	
	/**
	 * Returns BBCode object with the given tag.
	 * 
	 * @param	string		$tag
	 * @return	BBCode
	 */
	public static function getBBCodeByTag($tag) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_bbcode
			WHERE	bbcodeTag = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$tag]);
		$row = $statement->fetchArray();
		if (!$row) $row = [];
		
		return new self(null, $row);
	}
	
	/**
	 * Returns true if the given BBCode tag is allowed by the given list of
	 * BBCode tags. If the relevant BBCode should be globally disabled or non-
	 * existent, false is returned.
	 * 
	 * @param	string		$bbcodeTag
	 * @param	string[]	$allowedBBCodeTags
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
