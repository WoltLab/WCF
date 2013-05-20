<?php
namespace wcf\system\worker;
use wcf\data\user\UserEditor;
use wcf\data\user\UserList;
use wcf\data\user\UserProfileAction;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Worker implementation for updating user ranks.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	system.worker
 * @category	Community Framework
 */
class UserRankUpdateWorker extends AbstractWorker {
	/**
	 * @see	wcf\system\worker\AbstractWorker::$limit
	 */
	protected $limit = 100;
	
	/**
	 * @see	wcf\system\worker\IWorker::validate()
	 */
	public function validate() {
		WCF::getSession()->checkPermissions(array('admin.user.rank.canManageRank'));
	}
	
	/**
	 * @see	wcf\system\worker\IWorker::countObjects()
	 */
	public function countObjects() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user user";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$row = $statement->fetchArray();
		
		$this->count = $row['count'];
	}
	
	/**
	 * @see	wcf\system\worker\IWorker::execute()
	 */
	public function execute() {
		// get users
		$userList = new UserList();
		$userList->sqlLimit = $this->limit;
		$userList->sqlOffset = $this->limit * $this->loopCount;
		$userList->sqlOrderBy = 'user_table.userID';
		$userList->readObjects();
		
		$users = array();
		foreach ($userList as $user) {
			$users[] = new UserEditor($user);
		}
		
		if (!empty($users)) {
			$action = new UserProfileAction($users, 'updateUserRank');
			$action->executeAction();
			$action = new UserProfileAction($users, 'updateUserOnlineMarking');
			$action->executeAction();
		}
	}
	
	/**
	 * @see	wcf\system\worker\IWorker::getProceedURL()
	 */
	public function getProceedURL() {
		return LinkHandler::getInstance()->getLink('UserRankList');
	}
}
