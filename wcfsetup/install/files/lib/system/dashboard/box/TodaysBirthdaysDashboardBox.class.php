<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\user\UserProfileList;
use wcf\page\IPage;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\user\UserBirthdayCache;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Shows today's birthdays.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class TodaysBirthdaysDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * user profiles
	 * @var	array<\wcf\data\user\UserProfile>
	 */
	public $userProfiles = array();
	
	/**
	 * @see	\wcf\system\dashboard\box\IDashboardBox::init()
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);
		
		// get current date
		$currentDay = DateUtil::format(null, 'm-d');
		$date = explode('-', DateUtil::format(null, 'Y-n-j'));
		
		// get user ids
		$userIDs = UserBirthdayCache::getInstance()->getBirthdays($date[1], $date[2]);
		
		if (!empty($userIDs)) {
			$userOptions = UserOptionCacheBuilder::getInstance()->getData(array(), 'options');
			if (isset($userOptions['birthday'])) {
				$birthdayUserOption = $userOptions['birthday'];
				
				$userProfileList = new UserProfileList();
				$userProfileList->setObjectIDs($userIDs);
				$userProfileList->readObjects();
				$i = 0;
				foreach ($userProfileList as $userProfile) {
					if ($i == 10) break;
					
					$birthdayUserOption->setUser($userProfile->getDecoratedObject());
						
					if (!$userProfile->isProtected() && $birthdayUserOption->isVisible() && substr($userProfile->birthday, 5) == $currentDay) {
						$this->userProfiles[] = $userProfile;
						$i++;
					}
				}
			}
		}
		
		$this->fetched();
	}
	
	/**
	 * @see	\wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		if (empty($this->userProfiles)) {
			return '';
		}
		
		WCF::getTPL()->assign(array(
			'birthdayUserProfiles' => $this->userProfiles
		));
		return WCF::getTPL()->fetch('dashboardBoxTodaysBirthdays');
	}
}
