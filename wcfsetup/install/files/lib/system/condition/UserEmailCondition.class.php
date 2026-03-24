<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\WCF;

/**
 * Condition implementation for the email address of a user.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @implements IObjectListCondition<UserList>
 */
class UserEmailCondition extends AbstractTextCondition implements
    IContentCondition,
    IObjectListCondition,
    IUserCondition
{
    use TObjectListUserCondition;

    /**
     * @inheritDoc
     */
    protected $fieldName = 'email';

    /**
     * @inheritDoc
     */
    protected $label = 'wcf.user.email';

    #[\Override]
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        $objectList->getConditionBuilder()->add(
            'user_table.email LIKE ?',
            ['%' . \addcslashes($conditionData['email'], '_%') . '%']
        );
    }

    #[\Override]
    public function checkUser(Condition $condition, User $user)
    {
        return \str_contains($user->email, $condition->email);
    }

    #[\Override]
    public function showContent(Condition $condition)
    {
        if (!WCF::getUser()->userID) {
            return false;
        }

        return $this->checkUser($condition, WCF::getUser());
    }
}
