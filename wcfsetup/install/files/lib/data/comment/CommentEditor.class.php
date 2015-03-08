<?php
namespace wcf\data\comment;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit comments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment
 * @category	Community Framework
 */
class CommentEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\comment\Comment';
	
	/**
	 * Updates response ids.
	 */
	public function updateResponseIDs() {
		$sql = "SELECT		responseID
			FROM		wcf".WCF_N."_comment_response
			WHERE		commentID = ?
			ORDER BY	time ASC, responseID ASC";
		$statement = WCF::getDB()->prepareStatement($sql, 5);
		$statement->execute(array($this->commentID));
		$responseIDs = array();
		while ($row = $statement->fetchArray()) {
			$responseIDs[] = $row['responseID'];
		}
		
		$this->update(array(
			'responseIDs' => serialize($responseIDs)
		));
	}
}
