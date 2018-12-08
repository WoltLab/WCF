<?php
namespace wcf\system\box;
use wcf\data\user\trophy\UserTrophyList;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Box controller for a list of articles.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 * @since	3.1
 *
 * @property	UserTrophyList         $objectList
 */
class UserTrophyListBoxController extends AbstractDatabaseObjectListBoxController {
	/**
	 * @inheritDoc
	 */
	public $defaultLimit = 10;
	
	/**
	 * @inheritDoc
	 */
	public $maximumLimit = 50;
	
	/**
	 * @inheritDoc
	 */
	public $minimumLimit = 3;
	
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['sidebarLeft', 'sidebarRight', 'contentTop', 'contentBottom', 'top', 'bottom'];
	
	/**
	 * @inheritDoc
	 */
	public $sortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $sortField = 'time';
	
	/**
	 * @inheritDoc
	 */
	protected $conditionDefinition = 'com.woltlab.wcf.box.userTrophyList.condition';
	
	/**
	 * @inheritDoc
	 */
	protected function getObjectList() {
		$list = new UserTrophyList();
		
		if (!empty($list->sqlJoins)) $list->sqlJoins .= ' ';
		if (!empty($list->sqlConditionJoins)) $list->sqlConditionJoins .= ' ';
		$list->sqlJoins .= 'LEFT JOIN wcf'.WCF_N. '_trophy trophy ON user_trophy.trophyID = trophy.trophyID';
		$list->sqlConditionJoins .= 'LEFT JOIN wcf'.WCF_N.'_trophy trophy ON user_trophy.trophyID = trophy.trophyID';
		
		// trophy category join
		$list->sqlJoins .= ' LEFT JOIN wcf'.WCF_N.'_category category ON trophy.categoryID = category.categoryID';
		$list->sqlConditionJoins .= ' LEFT JOIN wcf'.WCF_N.'_category category ON trophy.categoryID = category.categoryID';
		
		$list->getConditionBuilder()->add('trophy.isDisabled = ?', [0]);
		$list->getConditionBuilder()->add('category.isDisabled = ?', [0]);
		
		if (!WCF::getUser()->userID) {
			$list->getConditionBuilder()->add('user_trophy.userID IN (SELECT userID FROM wcf'.WCF_N.'_user_option_value WHERE userOption'. UserOptionCacheBuilder::getInstance()->getData()['options']['canViewTrophies']->optionID .' = 0)');
		} else if (!WCF::getSession()->getPermission('admin.general.canViewPrivateUserOptions')) {
			$conditionBuilder = new PreparedStatementConditionBuilder(false, 'OR');
			$conditionBuilder->add('user_trophy.userID IN (SELECT userID FROM wcf'.WCF_N.'_user_option_value WHERE (userOption'. UserOptionCacheBuilder::getInstance()->getData()['options']['canViewTrophies']->optionID .' = 0 OR userOption'. UserOptionCacheBuilder::getInstance()->getData()['options']['canViewTrophies']->optionID .' = 1))');
			
			$friendshipConditionBuilder = new PreparedStatementConditionBuilder(false);
			$friendshipConditionBuilder->add('user_trophy.userID IN (SELECT userID FROM wcf'.WCF_N.'_user_option_value WHERE userOption'. UserOptionCacheBuilder::getInstance()->getData()['options']['canViewTrophies']->optionID .' = 2)');
			$friendshipConditionBuilder->add('user_trophy.userID IN (SELECT userID FROM wcf'.WCF_N.'_user_follow WHERE followUserID = ?)', [WCF::getUser()->userID]);
			$conditionBuilder->add('('.$friendshipConditionBuilder.')', $friendshipConditionBuilder->getParameters());
			$conditionBuilder->add('user_trophy.userID = ?', [WCF::getUser()->userID]);
			
			$list->getConditionBuilder()->add('('.$conditionBuilder.')', $conditionBuilder->getParameters());
		}
		
		return $list;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTemplate() {
		$userIDs = [];
		
		foreach ($this->objectList->getObjects() as $trophy) {
			$userIDs[] = $trophy->userID;
		}
		
		UserProfileRuntimeCache::getInstance()->cacheObjectIDs(array_unique($userIDs));
		
		return WCF::getTPL()->fetch('boxUserTrophyList', 'wcf', [
			'boxUserTrophyList' => $this->objectList,
			'boxPosition' => $this->box->position
		], true);
	}
}
