<?php
namespace wcf\system\worker;
use wcf\data\user\UserList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Worker implementation for update the user activity point database table's `items` column
 * and afterwards also the `activityPoints`
 * 
 * This worker is intended to run after the `UserRebuildDataWorker` so that the object counters
 * in the `wcf1_user` table are updated.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 * @since	5.2
 * 
 * @method	UserList	getObjectList()
 */
class UserActivityPointItemsRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = UserList::class;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 50;
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		if (count($this->getObjectList()) === 0) {
			return;
		}
		
		// update activity points for positive reactions
		$reactionObjectType = UserActivityPointHandler::getInstance()->getObjectTypeByName('com.woltlab.wcf.like.activityPointEvent.receivedLikes');
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('user_activity_point.objectTypeID = ?', [$reactionObjectType->objectTypeID]);
		$conditionBuilder->add('user_activity_point.userID IN (?)', [$this->getObjectList()->getObjectIDs()]);
		
		$sql = "UPDATE		wcf" . WCF_N . "_user_activity_point user_activity_point
			LEFT JOIN	wcf" . WCF_N . "_user user_table
			ON		(user_table.userID = user_activity_point.userID)
			SET		user_activity_point.items = user_table.likesReceived,
					user_activity_point.activityPoints = user_activity_point.items * ?
			" . $conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge(
			[$reactionObjectType->points],
			$conditionBuilder->getParameters()
		));
		
		// update activity points
		UserActivityPointHandler::getInstance()->updateUsers($this->getObjectList()->getObjectIDs());
	}
}
