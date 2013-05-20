<?php
namespace wcf\system\worker;
use wcf\system\request\LinkHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Worker implementation for updating user activity point caches.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	system.worker
 * @category	Community Framework
 */
class UserActivityPointUpdateCacheWorker extends AbstractWorker {
	/**
	 * @see	wcf\system\worker\AbstractWorker::$limit
	 */
	protected $limit = 50;
	
	/**
	 * @see	wcf\system\worker\IWorker::validate()
	 */
	public function validate() {
		WCF::getSession()->checkPermissions(array('admin.user.canEditActivityPoints'));
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
		$sql = "SELECT		userID
			FROM		wcf".WCF_N."_user user
			ORDER BY	user.userID";
		$statement = WCF::getDB()->prepareStatement($sql, $this->limit, ($this->limit * $this->loopCount));
		$statement->execute();
		
		$userIDs = array();
		while ($row = $statement->fetchArray()) {
			$userIDs[] = $row['userID'];
		}
		
		UserActivityPointHandler::getInstance()->updateUsers($userIDs);
	}
	
	/**
	 * @see	wcf\system\worker\IWorker::getProceedURL()
	 */
	public function getProceedURL() {
		return LinkHandler::getInstance()->getLink('UserActivityPointOption');
	}
}
