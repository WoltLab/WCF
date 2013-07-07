<?php
namespace wcf\system\importer;
use wcf\data\like\LikeEditor;

/**
 * Imports likes.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class AbstractLikeImporter implements IImporter {
	/**
	 * object type id for likes
	 * @var integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * @see wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data) {
		if ($data['objectUserID']) $data['objectUserID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['objectUserID']);
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		if (!$data['userID']) return 0;
		
		$like = LikeEditor::create(array_merge($data, array('objectTypeID' => $this->objectTypeID)));
		
		return $like->likeID;
	}
}
