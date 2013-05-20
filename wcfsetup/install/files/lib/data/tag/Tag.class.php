<?php
namespace wcf\data\tag;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Represents a tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	data.tag
 * @category	Community Framework
 */
class Tag extends DatabaseObject implements IRouteController {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'tag';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseIndexName
	 */
	protected static $databaseTableIndexName = 'tagID';
	
	/**
	 * Return the tag with the given name or null of no such tag exists.
	 *
	 * @param	string		$name
	 * @param	integer		$languageID
	 * @return	mixed
	 */
	public static function getTag($name, $languageID = 0) {
		$sql = "SELECT	*
			FROM 	wcf".WCF_N."_tag
			WHERE 	languageID = ?
				AND name = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($languageID, $name));
		$row = $statement->fetchArray();
		if ($row !== false) return new Tag(null, $row);
		
		return null;
	}
	
	/**
	 * Takes a string of comma separated tags and splits it into an array.
	 * 
	 * @param	string		$tags
	 * @param	string		$separators
	 * @return	array<string>
	 */
	public static function splitString($tags, $separators = ',;') {
		return array_unique(ArrayUtil::trim(preg_split('/['.preg_quote($separators).']/', $tags)));
	}
	
	/**
	 * Takes a list of tags and builds a comma separated string from it.
	 * 
	 * @param	array<mixed>	$tags
	 * @param	string		$separator
	 * @return	string
	 */
	public static function buildString(array $tags, $separator = ', ') {
		$string = '';
		foreach ($tags as $tag) {
			if (!empty($string)) $string .= $separator;
			$string .= (is_object($tag) ? $tag->__toString() : $tag);
		}
		
		return $string;
	}
	
	/**
	 * @see	wcf\data\ITitledObject::getTitle()
	 */
	public function getTitle() {
		return $this->name;
	}
	
	/**
	 * Returns the name of this tag.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->getTitle();
	}
}
