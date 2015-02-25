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
 */
class CommentResponseList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\comment\response\CommentResponse';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$sqlOrderBy
	 */
	public $sqlOrderBy = 'comment_response.time ASC';
}
