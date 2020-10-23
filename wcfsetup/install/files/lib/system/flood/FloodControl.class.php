<?php
namespace wcf\system\flood;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\UserUtil;

/**
 * Flood control tracks the times contents are created by users.
 * 
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Flood
 * @since       5.4
 */
class FloodControl extends SingletonFactory {
	/**
	 * Returns the number of contents by a certain identifier type within a certain
	 * period of time `[$time-$interval, $time]` and the earliest time within the period content
	 * was created.
	 */
	protected function countContentByIdentifier(string $objectType, string $identifier, \DateInterval $interval, int $time): array {
		$sql = "SELECT  COUNT(*) AS count, MIN(time) AS earliestTime
			FROM    wcf" . WCF_N . "_flood_control
			WHERE   objectTypeID = ?
			        AND identifier = ?
			        AND time >= ?
			        AND time <= ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$this->getObjectTypeID($objectType),
			$identifier,
			DateUtil::getDateTimeByTimestamp($time)->sub($interval)->getTimestamp(),
			$time,
		]);
		
		return $statement->fetchSingleRow();
	}
	
	/**
	 * Returns the number of contents the active user created of the given type within a certain
	 * period of time `[$time-$interval, $time]` and the earliest time within the period content
	 * was created.
	 */
	public function countContent(string $objectType, \DateInterval $interval, int $time = TIME_NOW): array {
		if (WCF::getUser()->userID) {
			return $this->countUserContent($objectType, WCF::getUser()->userID, $interval, $time);
		}
		else {
			return $this->countGuestContent($objectType, UserUtil::getIpAddress(), $interval, $time);
		}
	}
	
	/**
	 * Returns the number of contents a guest created of the given type within a certain period
	 * of time `[$time-$interval, $time]` and the earliest time within the period content was
	 * created.
	 */
	public function countGuestContent(string $objectType, string $ipAddress, \DateInterval $interval, int $time = TIME_NOW): array {
		return $this->countContentByIdentifier(
			$objectType,
			$this->getUserIdentifier($objectType, $ipAddress),
			$interval,
			$time
		);
	}
	
	/**
	 * Returns the number of contents a user created of the given type within a certain period
	 * of time `[$time-$interval, $time]` and the earliest time within the period content was
	 * created.
	 */
	public function countUserContent(string $objectType, int $userID, \DateInterval $interval, int $time = TIME_NOW): array {
		return $this->countContentByIdentifier(
			$objectType,
			$this->getUserIdentifier($objectType, $userID),
			$interval,
			$time
		);
	}
	
	/**
	 * Returns the identifier used for a guest with the given ip address for content of the
	 * given object type.
	 */
	protected function getGuestIdentifier(string $objectType, string $ipAddress): string {
		return \hash_hmac(
			'md5',
			'guest:' . $ipAddress,
			'wcf' . WCF_N. '_flood_log' . $objectType,
			true
		);
	}
	
	/**
	 * Returns the id of the given flood control object type.
	 * 
	 * @throws      \InvalidArgumentException       if the object type is invalid
	 */
	protected function getObjectTypeID(string $objectType): int {
		$objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName(
			'com.woltlab.wcf.floodControl',
			$objectType
		);
		if ($objectTypeID === null) {
			throw new \InvalidArgumentException("Unknown flood control object type '{$objectType}'.");
		}
		
		return $objectTypeID;
	}
	
	/**
	 * Returns the identifier used for a user with the given id for content of the given object
	 * type.
	 */
	protected function getUserIdentifier(string $objectType, int $userID): string {
		return \hash_hmac(
			'md5',
			'user:' . $userID,
			'wcf' . WCF_N. '_flood_log' . $objectType,
			true
		);
	}
	
	/**
	 * Creates a flood control entry.
	 */
	protected function registerContentByIdentifier(string $objectType, string $identifier, int $time): void {
		$sql = "INSERT INTO     wcf" . WCF_N . "_flood_control
			                (objectTypeID, identifier, time)
			VALUES          (?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$this->getObjectTypeID($objectType),
			$identifier,
			$time,
		]);
	}
	
	/**
	 * Registers content created by the active user.
	 */
	public function registerContent(string $objectType, int $time = TIME_NOW): void {
		if (WCF::getUser()->userID) {
			$this->registerUserContent($objectType, WCF::getUser()->userID, $time);
		}
		else {
			$this->registerGuestContent($objectType, UserUtil::getIpAddress(), $time);
		}
	}
	
	/**
	 * Registers content created by a guest.
	 */
	public function registerGuestContent(string $objectType, string $ipAddress, int $time = TIME_NOW): void {
		$this->registerContentByIdentifier(
			$objectType,
			$this->getGuestIdentifier($objectType, $ipAddress),
			$time
		);
	}
	
	/**
	 * Registers content created by a registered user.
	 */
	public function registerUserContent(string $objectType, int $userID, int $time = TIME_NOW): void {
		$this->registerContentByIdentifier(
			$objectType,
			$this->getUserIdentifier($objectType, $userID),
			$time
		);
	}
}
