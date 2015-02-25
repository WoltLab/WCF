<?php
namespace wcf\system\importer;
use wcf\system\WCF;

/**
 * Imports likes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class AbstractLikeImporter extends AbstractImporter {
	/**
	 * @see	\wcf\system\importer\AbstractImporter::$className
	 */
	protected $className = 'wcf\data\like\Like';
	
	/**
	 * object type id for likes
	 * @var	integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * @see	\wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		if ($data['objectUserID']) $data['objectUserID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['objectUserID']);
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		if (!$data['userID']) return 0;
		if (empty($data['time'])) $data['time'] = 1;
		
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_like
						(objectID, objectTypeID, objectUserID, userID, time, likeValue)
			VALUES			(?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$data['objectID'],
			$this->objectTypeID,
			$data['objectUserID'],
			$data['userID'],
			$data['time'],
			$data['likeValue']
		));
		
		return 0;
	}
}
