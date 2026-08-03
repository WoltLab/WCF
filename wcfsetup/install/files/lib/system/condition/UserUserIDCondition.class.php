<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\WCF;

/**
 * Condition implementation for the user id of a user.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @implements IObjectListCondition<UserList>
 */
class UserUserIDCondition extends AbstractSingleFieldCondition implements
    IContentCondition,
    IObjectListCondition,
    IUserCondition
{
    use TObjectListUserCondition;

    /**
     * @inheritDoc
     */
    protected $label = 'wcf.user.userID';

    /**
     * @var int|null
     */
    protected $userID;

    #[\Override]
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        $objectList->getConditionBuilder()->add('user_table.userID = ?', [$conditionData['userID']]);
    }

    #[\Override]
    public function checkUser(Condition $condition, User $user)
    {
        return $user->userID === $condition->userID;
    }

    #[\Override]
    public function showContent(Condition $condition)
    {
        if (WCF::getUser()->isGuest()) {
            return false;
        }

        return $this->checkUser($condition, WCF::getUser());
    }

    #[\Override]
    protected function getFieldElement()
    {
        return '<input type="number" name="userID" value="' . $this->userID . '" class="small">';
    }

    #[\Override]
    public function readFormParameters()
    {
        if (!empty($_POST['userID'])) {
            $this->userID = \intval($_POST['userID']);
        }
    }

    #[\Override]
    public function getData()
    {
        if ($this->userID !== null) {
            return ['userID' => $this->userID];
        }

        return null;
    }

    #[\Override]
    public function setData(Condition $condition)
    {
        $this->userID = $condition->conditionData['userID'];
    }
}
