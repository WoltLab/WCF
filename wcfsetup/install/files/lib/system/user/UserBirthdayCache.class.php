<?php
namespace wcf\system\user;
use wcf\system\cache\builder\UserBirthdayCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Manages the user birthday cache.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User
 */
class UserBirthdayCache extends SingletonFactory {
	/**
	 * loaded months
	 * @var	integer[]
	 */
	protected $monthsLoaded = [];
	
	/**
	 * user birthdays
	 * @var	integer[]
	 */
	protected $birthdays = [];
	
	/**
	 * Loads the birthday cache.
	 * 
	 * @param	integer		$month
	 */
	protected function loadMonth($month) {
		if (!isset($this->monthsLoaded[$month])) {
			$this->birthdays = array_merge($this->birthdays, UserBirthdayCacheBuilder::getInstance()->getData(['month' => $month]));
			$this->monthsLoaded[$month] = true;
		}
	}
	
	/**
	 * Gets the user birthdays for a specific day.
	 * 
	 * @param	integer		$month
	 * @param	integer		$day
	 * @return	integer[]	list of user ids
	 */
	public function getBirthdays($month, $day) {
		$this->loadMonth($month);
		
		$index = ($month < 10 ? '0' : '') . $month . '-' . ($day < 10 ? '0' : '') . $day;
		if (isset($this->birthdays[$index])) return $this->birthdays[$index];
		
		return [];
	}
}
