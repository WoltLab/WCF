<?php
namespace wcf\system\worker;
use wcf\data\like\Like;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Worker implementation for updating like users.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.worker
 * @category	Community Framework
 */
class LikeUserRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @see	\wcf\system\worker\AbstractRebuildDataWorker::$objectListClassName
	 */
	protected $objectListClassName = 'wcf\data\like\object\LikeObjectList';
	
	/**
	 * @see	\wcf\system\worker\AbstractWorker::$limit
	 */
	protected $limit = 100;
	
	/**
	 * @see	\wcf\system\worker\AbstractRebuildDataWorker::initObjectList
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'like_object.likeObjectID';
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::execute()
	 */
	public function execute() {
		parent::execute();
		
		if (!$this->loopCount) {
			// reset cached users
			$sql = "UPDATE	wcf".WCF_N."_like_object
				SET	cachedUsers = NULL";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
		}
		
		$sql = "SELECT		userID
			FROM		wcf".WCF_N."_like
			WHERE		objectID = ?
					AND objectTypeID = ?
					AND likeValue = ?
			ORDER BY	time DESC";
		$statement = WCF::getDB()->prepareStatement($sql, 3);
		$userData = $userIDs = array();
		foreach ($this->objectList as $likeObject) {
			$userData[$likeObject->likeObjectID] = array();
			
			$statement->execute(array(
				$likeObject->objectID,
				$likeObject->objectTypeID,
				Like::LIKE
			));
			while ($row = $statement->fetchArray()) {
				$userData[$likeObject->likeObjectID][] = $row['userID'];
				$userIDs[] = $row['userID'];
			}
		}
		
		if (empty($userIDs)) {
			return;
		}
		
		// fetch usernames
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", array($userIDs));
		$sql = "SELECT	userID, username
			FROM	wcf".WCF_N."_user
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$usernames = array();
		while ($row = $statement->fetchArray()) {
			$usernames[$row['userID']] = $row['username'];
		}
		
		// update like objects
		$sql = "UPDATE	wcf".WCF_N."_like_object
			SET	cachedUsers = ?
			WHERE	likeObjectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($userData as $likeObjectID => $data) {
			foreach ($data as &$value) {
				$value = array(
					'userID' => $value,
					'username' => $usernames[$value]
				);
			}
			unset($value);
			
			$statement->execute(array(
				serialize($data),
				$likeObjectID
			));
		}
		WCF::getDB()->commitTransaction();
	}
}
