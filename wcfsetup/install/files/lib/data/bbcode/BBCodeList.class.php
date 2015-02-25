<?php
namespace wcf\data\bbcode;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of bbcodes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.bbcode
 * @category	Community Framework
 */
class BBCodeList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\bbcode\BBCode';
}
