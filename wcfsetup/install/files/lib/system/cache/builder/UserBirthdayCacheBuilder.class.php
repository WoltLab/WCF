<?php
namespace wcf\system\cache\builder;
use wcf\data\user\User;
use wcf\system\WCF;

/**
 * Caches user birthdays (one cache file per month).
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class UserBirthdayCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected $maxLifetime = 3600;
	
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$userOptionID = User::getUserOptionID('birthday');
		if ($userOptionID === null) {
			// birthday profile field missing; skip
			return [];
		}
		
		$data = [];
		$birthday = 'userOption'.$userOptionID;
		$sql = "SELECT	userID, ".$birthday."
			FROM	wcf".WCF_N."_user_option_value
			WHERE	".$birthday." LIKE ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(['%-' . ($parameters['month'] < 10 ? '0' : '') . $parameters['month'] . '-%']);
		while ($row = $statement->fetchArray()) {
			list(, $month, $day) = explode('-', $row[$birthday]);
			if (!isset($data[$month . '-' . $day])) $data[$month . '-' . $day] = [];
			$data[$month . '-' . $day][] = $row['userID'];
		}
		
		return $data;
	}
}
