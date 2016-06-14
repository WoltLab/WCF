<?php
namespace wcf\data\comment;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of comments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Comment
 *
 * @method	Comment		current()
 * @method	Comment[]	getObjects()
 * @method	Comment|null	search($objectID)
 * @property	Comment[]	$objects
 */
class CommentList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Comment::class;
}
