<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileCache;
use wcf\page\IPage;
use wcf\system\user\UserBirthdayCache;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Shows today's birthdays of users the active user is following.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class TodaysFollowingBirthdaysDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * user profiles
	 * @var	UserProfile[]
	 */
	public $userProfiles = [];
	
	/**
	 * @inheritDoc
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);
		
		// get current date
		$currentDay = DateUtil::format(null, 'm-d');
		$date = explode('-', DateUtil::format(null, 'Y-n-j'));
		
		// get user ids
		$userIDs = UserBirthdayCache::getInstance()->getBirthdays($date[1], $date[2]);
		$userIDs = array_intersect($userIDs, WCF::getUserProfileHandler()->getFollowingUsers());
		
		if (!empty($userIDs)) {
			$userProfiles = UserProfileCache::getInstance()->getUserProfiles($userIDs);
			
			$i = 0;
			foreach ($userProfiles as $userProfile) {
				if ($i == 10) break;
				
				if (!$userProfile->isProtected() && substr($userProfile->birthday, 5) == $currentDay) {
					$this->userProfiles[] = $userProfile;
					$i++;
				}
			}
		}
		
		$this->fetched();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function render() {
		if (empty($this->userProfiles)) {
			return '';
		}
		
		WCF::getTPL()->assign([
			'birthdayUserProfiles' => $this->userProfiles
		]);
		return WCF::getTPL()->fetch('dashboardBoxTodaysFollowingBirthdays');
	}
}
