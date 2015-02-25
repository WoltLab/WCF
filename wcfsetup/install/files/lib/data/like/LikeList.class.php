<?php
namespace wcf\data\like;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of likes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.like
 * @category	Community Framework
 */
class LikeList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\like\Like';
}
