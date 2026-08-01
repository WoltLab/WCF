<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\WCF;

/**
 * Condition implementation for the cover photo of a user.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.3
 *
 * @implements IObjectListCondition<UserList>
 */
class UserCoverPhotoCondition extends AbstractSelectCondition implements
    IContentCondition,
    IObjectListCondition,
    IUserCondition
{
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
     * @var int
     */
    const NO_COVER_PHOTO = 0;

    /**
     * value of the "user has a cover photo" option
     * @var int
     */
    const COVER_PHOTO = 1;

    #[\Override]
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        switch ($conditionData['userCoverPhoto']) {
            case self::NO_COVER_PHOTO:
                $objectList->getConditionBuilder()->add(
                    '(user_table.coverPhotoFileID IS NULL)',
                );
                break;

            case self::COVER_PHOTO:
                $objectList->getConditionBuilder()->add(
                    '(user_table.coverPhotoFileID IS NOT NULL)',
                );
                break;
        }
    }

    #[\Override]
    public function checkUser(Condition $condition, User $user)
    {
        switch ($condition->userCoverPhoto) {
            case self::NO_COVER_PHOTO:
                return $user->coverPhotoFileID === null;

            case self::COVER_PHOTO:
                return $user->coverPhotoFileID !== null;
        }

        return false;
    }

    #[\Override]
    protected function getOptions()
    {
        return [
            self::NO_SELECTION_VALUE => 'wcf.global.noSelection',
            self::NO_COVER_PHOTO => 'wcf.user.condition.coverPhoto.noCoverPhoto',
            self::COVER_PHOTO => 'wcf.user.condition.coverPhoto.coverPhoto',
        ];
    }

    #[\Override]
    public function showContent(Condition $condition)
    {
        if (WCF::getUser()->isGuest()) {
            return false;
        }

        return $this->checkUser($condition, WCF::getUser());
    }
}
