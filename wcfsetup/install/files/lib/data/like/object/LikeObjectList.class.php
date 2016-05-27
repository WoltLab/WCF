<?php
namespace wcf\data\like\object;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of like objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.like.object
 * @category	Community Framework
 *
 * @method	LikeObject		current()
 * @method	LikeObject[]		getObjects()
 * @method	LikeObject|null		search($objectID)
 * @property	LikeObject[]		$objects
 */
class LikeObjectList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = LikeObject::class;
}
