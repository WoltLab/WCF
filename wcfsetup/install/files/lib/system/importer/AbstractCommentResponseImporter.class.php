<?php
namespace wcf\system\importer;
use wcf\data\comment\response\CommentResponseEditor;
use wcf\system\WCF;

/**
 * Imports comment responses.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
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
		
		// update parent comment
		$sql = "UPDATE	wcf".WCF_N."_comment
			SET	responseIDs = ?
			WHERE	commentID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			serialize(array($response->responseID)),
			$response->commentID
		));
		
		return $response->responseID;
	}
}
