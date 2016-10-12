<?php
namespace wcf\system\importer;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentEditor;

/**
 * Imports comments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class AbstractCommentImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = Comment::class;
	
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
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		
		$comment = CommentEditor::create(array_merge($data, ['objectTypeID' => $this->objectTypeID]));
		
		ImportHandler::getInstance()->saveNewID($this->objectTypeName, $oldID, $comment->commentID);
		
		return $comment->commentID;
	}
}
