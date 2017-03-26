<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Condition implementation for the avatar of a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 */
class UserAvatarCondition extends AbstractSelectCondition implements IContentCondition, IObjectListCondition, IUserCondition {
	use TObjectListUserCondition;
	
	/**
	 * @inheritDoc
	 */
	protected $fieldName = 'userAvatar';
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".UserList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		switch ($conditionData['userAvatar']) {
			case self::NO_AVATAR:
				$objectList->getConditionBuilder()->add('user_table.avatarID IS NULL');
				$objectList->getConditionBuilder()->add('user_table.enableGravatar = ?', [0]);
			break;
			
			case self::AVATAR:
				$objectList->getConditionBuilder()->add('user_table.avatarID IS NOT NULL');
			break;
			
			case self::GRAVATAR:
				$objectList->getConditionBuilder()->add('user_table.enableGravatar = ?', [1]);
			break;
		}
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	protected function getOptions() {
		$options = [
			self::NO_SELECTION_VALUE => 'wcf.global.noSelection',
			self::NO_AVATAR => 'wcf.user.condition.avatar.noAvatar',
			self::AVATAR => 'wcf.user.condition.avatar.avatar'
		];
		if (MODULE_GRAVATAR) {
			$options[self::GRAVATAR] = 'wcf.user.condition.avatar.gravatar';
		}
		
		return $options;
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		if (!WCF::getUser()->userID) return false;
		
		return $this->checkUser($condition, WCF::getUser());
	}
}
