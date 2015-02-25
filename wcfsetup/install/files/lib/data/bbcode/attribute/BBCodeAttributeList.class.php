<?php
namespace wcf\data\bbcode\attribute;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of bbcode attribute.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.bbcode.attribute
 * @category	Community Framework
 */
class BBCodeAttributeList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\bbcode\attribute\BBCodeAttribute';
}
