<?php
namespace wcf\system\cache\builder;
use wcf\data\user\User;
use wcf\system\cache\builder\AbstractCacheBuilder;
use wcf\system\WCF;

/**
 * Caches user birthdays (one cache file per month).
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class UserBirthdayCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::$maxLifetime
	 */
	protected $maxLifetime = 3600;
	
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$userOptionID = User::getUserOptionID('birthday');
		if ($userOptionID === null) {
			// birthday profile field missing; skip
			return array();
		}
		
		$data = array();
		$birthday = 'userOption'.$userOptionID;
		$sql = "SELECT	userID, ".$birthday."
			FROM	wcf".WCF_N."_user_option_value
			WHERE	".$birthday." LIKE ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array('%-' . ($parameters['month'] < 10 ? '0' : '') . $parameters['month'] . '-%'));
		while ($row = $statement->fetchArray()) {
			list($year, $month, $day) = explode('-', $row[$birthday]);
			if (!isset($data[$month . '-' . $day])) $data[$month . '-' . $day] = array();
			$data[$month . '-' . $day][] = $row['userID'];
		}
		
		return $data;
	}
}
