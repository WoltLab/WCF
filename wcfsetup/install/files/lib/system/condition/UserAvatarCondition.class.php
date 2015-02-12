<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\WCF;

/**
 * Condition implementation for the avatar of a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class UserAvatarCondition extends AbstractSelectCondition implements IContentCondition, IUserCondition {
	/**
	 * @see	wcf\system\condition\AbstractSelectCondition::$fieldName
	 */
	protected $fieldName = 'userAvatar';
	
	/**
	 * @see	\wcf\system\condition\AbstractSingleFieldCondition::$label
	 */
	protected $label = 'wcf.user.condition.avatar';
	
	/**
	 * value of the "user has no avatar" option
	 * @var	integer
	 */
	const NO_AVATAR = 0;
	
	/**
	 * value of the "user has a custom avatar" option
	 * @var	integer
	 */
	const AVATAR = 1;
	
	/**
	 * value of the "user has a gravatar" option
	 * @var	integer
	 */
	const GRAVATAR = 2;
	
	/**
	 * @see	\wcf\system\condition\IUserCondition::addUserCondition()
	 */
	public function addUserCondition(Condition $condition, UserList $userList) {
		switch ($condition->userAvatar) {
			case self::NO_AVATAR:
				$userList->getConditionBuilder()->add('user_table.avatarID IS NULL');
				$userList->getConditionBuilder()->add('user_table.enableGravatar = ?', array(0));
			break;
			
			case self::AVATAR:
				$userList->getConditionBuilder()->add('user_table.avatarID IS NOT NULL');
			break;
			
			case self::GRAVATAR:
				$userList->getConditionBuilder()->add('user_table.enableGravatar = ?', array(1));
			break;
		}
	}
	
	/**
	 * @see	\wcf\system\condition\IUserCondition::checkUser()
	 */
	public function checkUser(Condition $condition, User $user) {
		switch ($condition->userAvatar) {
			case self::NO_AVATAR:
				return !$user->avatarID && !$user->enableGravatar;
			break;
			
			case self::AVATAR:
				return $user->avatarID != 0;
			break;
			
			case self::GRAVATAR:
				return $user->enableGravatar;
			break;
		}
	}
	
	/**
	 * @see	\wcf\system\condition\AbstractSelectCondition::getOptions()
	 */
	protected function getOptions() {
		return array(
			self::NO_SELECTION_VALUE => 'wcf.global.noSelection',
			self::NO_AVATAR => 'wcf.user.condition.avatar.noAvatar',
			self::AVATAR => 'wcf.user.condition.avatar.avatar',
			self::GRAVATAR => 'wcf.user.condition.avatar.gravatar'
		);
	}
	
	/**
	 * @see	\wcf\system\condition\IContentCondition::showContent()
	 */
	public function showContent(Condition $condition) {
		if (!WCF::getUser()->userID) return false;
		
		return $this->checkUser($condition, WCF::getUser());
	}
}
