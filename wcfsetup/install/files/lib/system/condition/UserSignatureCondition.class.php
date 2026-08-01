<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\WCF;

/**
 * Condition implementation for the signature of a user.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.3
 *
 * @implements IObjectListCondition<UserList>
 */
class UserSignatureCondition extends AbstractSelectCondition implements
    IContentCondition,
    IObjectListCondition,
    IUserCondition
{
    use TObjectListUserCondition;

    /**
     * @inheritDoc
     */
    protected $fieldName = 'userSignature';

    /**
     * @inheritDoc
     */
    protected $label = 'wcf.user.condition.signature';

    /**
     * value of the "user has no signature" option
     * @var int
     */
    const NO_SIGNATURE = 0;

    /**
     * value of the "user has a signature" option
     * @var int
     */
    const SIGNATURE = 1;

    #[\Override]
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        switch ($conditionData['userSignature']) {
            case self::NO_SIGNATURE:
                $objectList->getConditionBuilder()->add(
                    '(user_table.signature = ? OR user_table.signature IS NULL)',
                    ['']
                );
                break;

            case self::SIGNATURE:
                $objectList->getConditionBuilder()->add(
                    '(user_table.signature <> ? AND user_table.signature IS NOT NULL)',
                    ['']
                );
                break;
        }
    }

    #[\Override]
    public function checkUser(Condition $condition, User $user)
    {
        switch ($condition->userSignature) {
            case self::NO_SIGNATURE:
                return $user->signature === '' || $user->signature === null;

            case self::SIGNATURE:
                return $user->signature !== '' && $user->signature !== null;
        }

        return false;
    }

    #[\Override]
    protected function getOptions()
    {
        return [
            self::NO_SELECTION_VALUE => 'wcf.global.noSelection',
            self::NO_SIGNATURE => 'wcf.user.condition.signature.noSignature',
            self::SIGNATURE => 'wcf.user.condition.signature.signature',
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
