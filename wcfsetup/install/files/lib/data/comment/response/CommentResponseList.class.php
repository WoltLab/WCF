<?php
namespace wcf\data\comment\response;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of comment responses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment.response
 * @category	Community Framework
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
