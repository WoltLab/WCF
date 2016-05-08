<?php
namespace wcf\system\importer;
use wcf\data\comment\response\CommentResponseEditor;
use wcf\system\WCF;

/**
 * Imports comment responses.
 * 
 * @author	Tim Duesterhus, Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class AbstractCommentResponseImporter extends AbstractImporter {
	/**
	 * @see	\wcf\system\importer\AbstractImporter::$className
	 */
	protected $className = 'wcf\data\comment\response\CommentResponse';
	
	/**
	 * object type name
	 * @var	string
	 */
	protected $objectTypeName = '';
	
	/**
	 * @see	\wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		
		$data['commentID'] = ImportHandler::getInstance()->getNewID($this->objectTypeName, $data['commentID']);
		if (!$data['commentID']) return 0;
		
		$response = CommentResponseEditor::create($data);
		
		$sql = "SELECT		responseID
			FROM		wcf".WCF_N."_comment_response
			WHERE		commentID = ?
			ORDER BY	time ASC, responseID ASC";
		$statement = WCF::getDB()->prepareStatement($sql, 5);
		$statement->execute(array($response->commentID));
		$responseIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		// update parent comment
		$sql = "UPDATE	wcf".WCF_N."_comment
			SET	responseIDs = ?,
				responses = responses + 1
			WHERE	commentID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			serialize($responseIDs),
			$response->commentID
		));
		
		return $response->responseID;
	}
}
