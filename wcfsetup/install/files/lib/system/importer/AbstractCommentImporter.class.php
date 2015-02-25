<?php
namespace wcf\system\importer;
use wcf\data\comment\CommentEditor;

/**
 * Imports comments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class AbstractCommentImporter extends AbstractImporter {
	/**
	 * @see	\wcf\system\importer\AbstractImporter::$className
	 */
	protected $className = 'wcf\data\comment\Comment';
	
	/**
	 * object type id for comments
	 * @var	integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * object type name
	 * @var	integer
	 */
	protected $objectTypeName = '';
	
	/**
	 * @see	\wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		
		$comment = CommentEditor::create(array_merge($data, array('objectTypeID' => $this->objectTypeID)));
		
		ImportHandler::getInstance()->saveNewID($this->objectTypeName, $oldID, $comment->commentID);
		
		return $comment->commentID;
	}
}
