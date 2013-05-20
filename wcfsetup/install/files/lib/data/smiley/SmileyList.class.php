<?php
namespace wcf\data\smiley;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of smilies.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.bbcode
 * @subpackage	data.smiley
 * @category	Community Framework
 */
class SmileyList extends DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\smiley\Smiley';
}
