<?php
namespace wcf\data\bbcode\attribute;
use wcf\data\bbcode\BBCode;
use wcf\data\DatabaseObject;

/**
 * Represents a bbcode attribute.
 * 
 * @author	Tim Duesterhus, Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Bbcode\Attribute
 *
 * @property-read	integer		$attributeID		unique id of the bbcode attribute
 * @property-read	integer		$bbcodeID		id of the bbcode the attribute belongs to
 * @property-read	integer		$attributeNo		number of bbcode attribute
 * @property-read	string		$attributeHtml		html code used to render the bbcode attribute or empty if no such html code exists
 * @property-read	string		$validationPattern	regular expression used to validate the bbcode attribute's value or empty if no such regular expression exists
 * @property-read	integer		$required		is `1` if the bbcode attribute is required of the bbcode, otherwise `0`
 * @property-read	integer		$useText		is `1` if the bbcode's content will be used as the bbcode attribute value
 */
class BBCodeAttribute extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'bbcode_attribute';
	
	/**
	 * Reads attributes by assigned bbcode.
	 * 
	 * @param	BBCode		$bbcode
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
