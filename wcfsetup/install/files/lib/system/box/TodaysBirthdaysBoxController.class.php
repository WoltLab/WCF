<?php
namespace wcf\system\box;
use wcf\data\user\option\UserOption;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\user\UserBirthdayCache;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Shows today's birthdays.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 * @since	3.0
 */
class TodaysBirthdaysBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * template name
	 * @var string
	 */
	protected $templateName = 'boxTodaysBirthdays';
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		// get current date
		$currentDay = DateUtil::format(null, 'm-d');
		$date = explode('-', DateUtil::format(null, 'Y-n-j'));
		
		// get user ids
		$userIDs = UserBirthdayCache::getInstance()->getBirthdays($date[1], $date[2]);
		$this->filterUserIDs($userIDs);
		
		if (!empty($userIDs)) {
			$userOptions = UserOptionCacheBuilder::getInstance()->getData([], 'options');
			if (isset($userOptions['birthday'])) {
				/** @var UserOption $birthdayUserOption */
				$birthdayUserOption = $userOptions['birthday'];
				
				$userProfiles = UserProfileRuntimeCache::getInstance()->getObjects($userIDs);
				$visibleUserProfiles = [];
				
				$i = 0;
				foreach ($userProfiles as $userProfile) {
					if ($i == 10) break;
					
					$birthdayUserOption->setUser($userProfile->getDecoratedObject());
					
					if (!$userProfile->isProtected() && $birthdayUserOption->isVisible() && substr($userProfile->birthday, 5) == $currentDay) {
						$visibleUserProfiles[] = $userProfile;
						$i++;
					}
				}
				
				if (!empty($visibleUserProfiles)) {
					WCF::getTPL()->assign([
						'birthdayUserProfiles' => $visibleUserProfiles
					]);
					$this->content = WCF::getTPL()->fetch($this->templateName);
				}
			}
		}
	}
	
	/**
	 * Filters given user ids.
	 * 
	 * @param	integer[]	$userIDs
	 */
	protected function filterUserIDs(&$userIDs) {
		// does nothing, can be overwritten by child classes
	}
}
