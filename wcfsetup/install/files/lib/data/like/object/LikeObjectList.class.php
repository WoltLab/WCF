<?php
namespace wcf\data\like\object;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of like objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.like.object
 * @category	Community Framework
 */
class LikeObjectList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\like\object\LikeObject';
}
