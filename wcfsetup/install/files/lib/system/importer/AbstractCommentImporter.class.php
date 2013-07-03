<?php
namespace wcf\system\importer;
use wcf\data\comment\CommentAction;

/**
 * Imports comments.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class AbstractCommentImporter implements IImporter {
	/**
	 * object type id for comments
	 * @var integer
	 */
	protected $objectTypeID = 0;
	
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
		
		$action = new CommentAction(array(), 'create', array(
			'data' => array_merge($data, array('objectTypeID' => $this->objectTypeID))		
		));
		$returnValues = $action->executeAction();
		$newID = $returnValues['returnValues']->commentID;
		
		ImportHandler::getInstance()->saveNewID($this->objectTypeName, $oldID, $newID);
		
		return $newID;
	}
}
