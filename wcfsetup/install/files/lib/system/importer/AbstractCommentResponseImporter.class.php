<?php
namespace wcf\system\importer;
use wcf\data\comment\response\CommentResponseAction;

/**
 * Imports comment responses.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class AbstractCommentResponseImporter implements IImporter {
	/**
	 * object type name
	 * @var string
	 */
	protected $objectTypeName = '';
	
	/**
	 * @see wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		if ($data['userID']) $data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		
		$data['commentID'] = ImportHandler::getInstance()->getNewID($this->objectTypeName, $data['commentID']);
		if (!$data['commentID']) return 0;
		
		$action = new CommentResponseAction(array(), 'create', array(
			'data' => $data		
		));
		$returnValues = $action->executeAction();
		$newID = $returnValues['returnValues']->responseID;
		
		return $newID;
	}
}
