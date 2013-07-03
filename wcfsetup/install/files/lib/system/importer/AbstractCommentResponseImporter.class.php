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
	 * @var integer
	 */
	protected $objectTypeName = '';
	
	/**
	 * @see wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data) {
		if ($data['userID']) $data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		if (!$data['userID']) $data['userID'] = null;
		
		$data['commentID'] = ImportHandler::getInstance()->getNewID($this->objectTypeName, $data['commentID']);
		if (!$data['commentID']) return 0;
		
		$action = new CommentResponseAction(array(), 'create', array(
			'data' => array_merge($data, array('objectTypeID' => $this->objectTypeID))		
		));
		$returnValues = $action->executeAction();
		$newID = $returnValues['returnValues']->responseID;
		
		return $newID;
	}
}
