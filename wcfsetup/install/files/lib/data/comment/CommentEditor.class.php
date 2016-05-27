<?php
namespace wcf\data\comment;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit comments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment
 * @category	Community Framework
 * 
 * @method	Comment		getDecoratedObject()
 * @mixin	Comment
 */
class CommentEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Comment::class;
	
	/**
	 * Updates response ids.
	 */
	public function updateResponseIDs() {
		$sql = "SELECT		responseID
			FROM		wcf".WCF_N."_comment_response
			WHERE		commentID = ?
			ORDER BY	time ASC, responseID ASC";
		$statement = WCF::getDB()->prepareStatement($sql, 5);
		$statement->execute([$this->commentID]);
		$responseIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		$this->update(['responseIDs' => serialize($responseIDs)]);
	}
}
