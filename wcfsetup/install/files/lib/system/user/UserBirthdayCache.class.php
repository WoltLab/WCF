<?php
namespace wcf\system\user;
use wcf\system\cache\builder\UserBirthdayCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Manages the user birthday cache.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user
 * @category	Community Framework
 */
class UserBirthdayCache extends SingletonFactory {
	/**
	 * loaded months
	 * @var	array<integer>
	 */
	protected $monthsLoaded = array();
	
	/**
	 * user birthdays
	 * @var	array<integer>
	 */
	protected $birthdays = array();
	
	/**
	 * Loads the birthday cache.
	 * 
	 * @param	integer		$month
	 */
	protected function loadMonth($month) {
		if (!isset($this->monthsLoaded[$month])) {
			$this->birthdays = array_merge($this->birthdays, UserBirthdayCacheBuilder::getInstance()->getData(array('month' => $month)));
			$this->monthsLoaded[$month] = true;
		}
	}
	
	/**
	 * Gets the user birthdays for a specific day.
	 * 
	 * @param	integer		$month
	 * @param	integer		$day
	 * @return	array<integer>	list of user ids
	 */
	public function getBirthdays($month, $day) {
		$this->loadMonth($month);
		
		$index = ($month < 10 ? '0' : '') . $month . '-' . ($day < 10 ? '0' : '') . $day;
		if (isset($this->birthdays[$index])) return $this->birthdays[$index];
		
		return array();
	}
}
