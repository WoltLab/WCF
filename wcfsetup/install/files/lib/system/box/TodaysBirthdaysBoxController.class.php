<?php
namespace wcf\system\box;
use wcf\data\DatabaseObject;
use wcf\data\user\option\UserOption;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\condition\ICondition;
use wcf\system\condition\IObjectCondition;
use wcf\system\condition\IObjectListCondition;
use wcf\system\user\UserBirthdayCache;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Shows today's birthdays.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 * @since	3.0
 */
class TodaysBirthdaysBoxController extends AbstractDatabaseObjectListBoxController {
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 * @since       5.3
	 */
	protected $conditionDefinition = 'com.woltlab.wcf.box.todaysBirthdays.condition';
	
	/**
	 * template name
	 * @var string
	 */
	protected $templateName = 'boxTodaysBirthdays';
	
	/**
	 * @inheritDoc
	 */
	public $defaultLimit = 5;
	
	/**
	 * @inheritDoc
	 */
	protected $sortFieldLanguageItemPrefix = 'wcf.user.sortField';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = [
		'username',
		'activityPoints',
		'registrationDate'
	];
	
	/**
	 * @inheritDoc
	 */
	protected function getObjectList() {}
	
	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {}
	
	/**
	 * @inheritDoc
	 */
	public function hasContent() {
		parent::hasContent();
		
		return AbstractBoxController::hasContent();
	}
	
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
					// ignore deleted users
					if ($userProfile === null) continue;
					
					// show a maximum of x users
					if ($i == $this->limit) break;
					
					foreach ($this->box->getConditions() as $condition) {
						/** @var IObjectCondition $processor */
						$processor = $condition->getObjectType()->getProcessor();
						if (!$processor->checkObject($userProfile->getDecoratedObject(), $condition->conditionData)) {
							continue 2;
						}
					}
					
					$birthdayUserOption->setUser($userProfile->getDecoratedObject());
					
					if (!$userProfile->isProtected() && $birthdayUserOption->isVisible() && substr($userProfile->birthday, 5) == $currentDay) {
						$visibleUserProfiles[] = $userProfile;
						$i++;
					}
				}
				
				if (!empty($visibleUserProfiles)) {
					// sort users
					DatabaseObject::sort($visibleUserProfiles, $this->sortField, $this->sortOrder);
					
					$this->content = WCF::getTPL()->fetch($this->templateName, 'wcf', [
						'birthdayUserProfiles' => $visibleUserProfiles
					], true);
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
