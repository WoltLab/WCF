<?php
namespace wcf\data\comment\response;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of comment responses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Comment\Response
 *
 * @method	CommentResponse		current()
 * @method	CommentResponse[]	getObjects()
 * @method	CommentResponse|null	search($objectID)
 * @property	CommentResponse[]	$objects
 */
class CommentResponseList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = CommentResponse::class;
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'comment_response.time ASC';
}
