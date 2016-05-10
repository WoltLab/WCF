<?php
namespace wcf\data\like;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of likes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.like
 * @category	Community Framework
 *
 * @method	Like		current()
 * @method	Like[]		getObjects()
 * @method	Like|null	search($objectID)
 * @property	Like[]		$objects
 */
class LikeList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Like::class;
}
