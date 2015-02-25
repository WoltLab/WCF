<?php
namespace wcf\data\bbcode\attribute;
use wcf\data\bbcode\BBCode;
use wcf\data\DatabaseObject;

/**
 * Represents a bbcode attribute.
 * 
 * @author	Tim Duesterhus, Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.bbcode.attribute
 * @category	Community Framework
 */
class BBCodeAttribute extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'bbcode_attribute';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'attributeID';
	
	/**
	 * Reads attributes by assigned bbcode.
	 * 
	 * @param	\wcf\data\bbcode\BBCode		$bbcode
	 * @return	array<\wcf\data\bbcode\attribute\BBCodeAttribute>
	 */
	public static function getAttributesByBBCode(BBCode $bbcode) {
		$attributeList = new BBCodeAttributeList();
		$attributeList->sqlOrderBy = "bbcode_attribute.attributeNo ASC";
		$attributeList->getConditionBuilder()->add('bbcode_attribute.bbcodeID = ?', array($bbcode->bbcodeID));
		$attributeList->readObjects();
		return $attributeList->getObjects();
	}
}
