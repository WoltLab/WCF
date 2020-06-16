<?php
namespace wcf\system\worker;
use wcf\data\like\object\LikeObjectList;
use wcf\data\like\Like;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

$_ = \wcf\functions\deprecatedClass(LikeUserRebuildDataWorker::class);
/**
 * Worker implementation for updating like users.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 * @deprecated  5.2 The worker has been removed from the ACP due to the replacement of the like
 * 		system by the reaction system. This class does not have a direct replacement.
 * 
 * @method	LikeObjectList	getObjectList()
 */
class LikeUserRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = LikeObjectList::class;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 100;
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'like_object.likeObjectID';
	}
	
	/**
	 * @inheritDoc
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
		$userData = $userIDs = [];
		foreach ($this->objectList as $likeObject) {
			$userData[$likeObject->likeObjectID] = [];
			
			$statement->execute([
				$likeObject->objectID,
				$likeObject->objectTypeID,
				Like::LIKE
			]);
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
		$conditions->add("userID IN (?)", [$userIDs]);
		$sql = "SELECT	userID, username
			FROM	wcf".WCF_N."_user
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$usernames = $statement->fetchMap('userID', 'username');
		
		// update like objects
		$sql = "UPDATE	wcf".WCF_N."_like_object
			SET	cachedUsers = ?
			WHERE	likeObjectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($userData as $likeObjectID => $data) {
			foreach ($data as &$value) {
				$value = [
					'userID' => $value,
					'username' => $usernames[$value]
				];
			}
			unset($value);
			
			$statement->execute([
				serialize($data),
				$likeObjectID
			]);
		}
		WCF::getDB()->commitTransaction();
	}
}
