<?php
namespace wcf\system\user\activity\point;
use wcf\data\like\Like;
use wcf\data\object\type\ObjectType;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Updates events for received likes.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.activity.point
 * @category	Community Framework
 */
class ReceivedLikesUserActivityPointObjectProcessor extends AbstractUserActivityPointObjectProcessor {
	/**
	 * @see	wcf\system\user\activity\point\IUserActivityPointObjectProcessor::countRequests();
	 */
	public function countRequests() {
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_like
			WHERE	likeValue = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(Like::LIKE));
		
		return ceil($statement->fetchColumn() / $this->limit) + 1;
	}
	
	/**
	 * @see	wcf\system\user\activity\point\IUserActivityPointObjectProcessor::updateActivityPointEvents();
	 */
	public function updateActivityPointEvents($request) {
		if ($request == 0) {
			// first request
			$sql = "DELETE FROM	wcf".WCF_N."_user_activity_point_event 
				WHERE		objectTypeID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->objectTypeID));
		}
		else {
			$sql = "SELECT		likeID
				FROM		wcf".WCF_N."_like
				WHERE		likeValue = ?
				ORDER BY	likeID ASC";
			$statement = WCF::getDB()->prepareStatement($sql, $this->limit, ($this->limit * ($request - 1)));
			$statement->execute(array(Like::LIKE));
			$likeIDs = array();
			while ($row = $statement->fetchArray()) {
				$likeIDs[] = $row['likeID'];
			}
			
			if (empty($likeIDs)) return;
			
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add("objectTypeID = ?", array($this->objectTypeID));
			$conditionBuilder->add("objectID IN (?)", array($likeIDs));
			
			// avoid problems with duplicate keys, as likes may be created in the meantime
			$sql = "DELETE FROM	wcf".WCF_N."_user_activity_point_event 
				".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
			
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add("likeID IN (?)", array($likeIDs));
			
			// use INSERT ... SELECT as this makes bulk updating easier
			$sql = "INSERT INTO	wcf".WCF_N."_user_activity_point_event
						(userID, objectTypeID, objectID, additionalData)
				SELECT		objectUserID AS userID,
						?,
						likeID AS objectID,
						?
				FROM	wcf".WCF_N."_like
				".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array_merge(array(
				$this->objectTypeID,
				serialize(array())
			), $conditionBuilder->getParameters()));
		}
	}
}
