<?php
namespace wcf\data\like;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of likes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Like
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
