<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\data\DatabaseObjectList;
use wcf\system\exception\InvalidObjectArgument;
use wcf\system\WCF;

/**
 * Condition implementation for the cover photo of a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 * @since	5.3
 */
class UserCoverPhotoCondition extends AbstractSelectCondition implements IContentCondition, IObjectListCondition, IUserCondition {
	use TObjectListUserCondition;
	
	/**
	 * @inheritDoc
	 */
	protected $fieldName = 'userCoverPhoto';
	
	/**
	 * @inheritDoc
	 */
	protected $label = 'wcf.user.condition.coverPhoto';
	
	/**
	 * value of the "user has no cover photo" option
	 * @var	integer
	 */
	const NO_COVER_PHOTO = 0;
	
	/**
	 * value of the "user has a cover photo" option
	 * @var	integer
	 */
	const COVER_PHOTO = 1;
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) {
			throw new InvalidObjectArgument($objectList, UserList::class, 'Object list');
		}
		
		switch ($conditionData['userCoverPhoto']) {
			case self::NO_COVER_PHOTO:
				$objectList->getConditionBuilder()->add('(user_table.coverPhotoHash = ? OR user_table.coverPhotoHash IS NULL)', ['']);
			break;
			
			case self::COVER_PHOTO:
				$objectList->getConditionBuilder()->add('(user_table.coverPhotoHash <> ? AND user_table.coverPhotoHash IS NOT NULL)', ['']);
			break;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkUser(Condition $condition, User $user) {
		switch ($condition->userCoverPhoto) {
			case self::NO_COVER_PHOTO:
				return $user->coverPhotoExtension === '' || $user->coverPhotoExtension === null;
			break;
			
			case self::COVER_PHOTO:
				return $user->coverPhotoExtension !== '' && $user->coverPhotoExtension !== null;
			break;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getOptions() {
		return [
			self::NO_SELECTION_VALUE => 'wcf.global.noSelection',
			self::NO_COVER_PHOTO => 'wcf.user.condition.coverPhoto.noCoverPhoto',
			self::COVER_PHOTO => 'wcf.user.condition.coverPhoto.coverPhoto'
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		if (!WCF::getUser()->userID) return false;
		
		return $this->checkUser($condition, WCF::getUser());
	}
}
