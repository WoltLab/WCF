<?php
namespace wcf\data\bbcode\attribute;
use wcf\data\bbcode\BBCode;
use wcf\data\DatabaseObject;

/**
 * Represents a bbcode attribute.
 * 
 * @author	Tim Duesterhus, Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Bbcode\Attribute
 *
 * @property-read	integer		$attributeID
 * @property-read	integer		$bbcodeID
 * @property-read	integer		$attributeNo
 * @property-read	string		$attributeHtml
 * @property-read	string		$validationPattern
 * @property-read	integer		$required
 * @property-read	integer		$useText
 */
class BBCodeAttribute extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'bbcode_attribute';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'attributeID';
	
	/**
	 * Reads attributes by assigned bbcode.
	 * 
	 * @param	\wcf\data\bbcode\BBCode		$bbcode
	 * @return	BBCodeAttribute[]
	 */
	public static function getAttributesByBBCode(BBCode $bbcode) {
		$attributeList = new BBCodeAttributeList();
		$attributeList->sqlOrderBy = "bbcode_attribute.attributeNo ASC";
		$attributeList->getConditionBuilder()->add('bbcode_attribute.bbcodeID = ?', [$bbcode->bbcodeID]);
		$attributeList->readObjects();
		return $attributeList->getObjects();
	}
}
