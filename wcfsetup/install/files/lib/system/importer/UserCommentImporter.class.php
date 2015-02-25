<?php
namespace wcf\system\importer;
use wcf\data\object\type\ObjectTypeCache;

/**
 * Imports user profile comments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class UserCommentImporter extends AbstractCommentImporter {
	/**
	 * @see	\wcf\system\importer\AbstractCommentImporter::$objectTypeName
	 */
	protected $objectTypeName = 'com.woltlab.wcf.user.comment';
	
	/**
	 * Creates a new UserCommentImporter object.
	 */
	public function __construct() {
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.comment.commentableContent', 'com.woltlab.wcf.user.profileComment');
		$this->objectTypeID = $objectType->objectTypeID;
	}
	
	/**
	 * @see	\wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		$data['objectID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['objectID']);
		if (!$data['objectID']) return 0;
		
		return parent::import($oldID, $data);
	}
}
