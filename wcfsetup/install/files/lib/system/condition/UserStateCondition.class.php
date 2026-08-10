<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Condition implementation for the state (banned, enabled) of a user.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @implements IObjectListCondition<UserList>
 */
class UserStateCondition extends AbstractSingleFieldCondition implements
    IContentCondition,
    IObjectListCondition,
    IUserCondition
{
    use TObjectListUserCondition;

    /**
     * @inheritDoc
     */
    protected $label = 'wcf.user.condition.state';

    /**
     * true if the the user has to be banned
     * @var int
     */
    protected $userIsBanned = 0;

    /**
     * true if the user has to be disabled
     * @var int
     */
    protected $userIsDisabled = 0;

    /**
     * true if the user has to be enabled
     * @var int
     */
    protected $userIsEnabled = 0;

    /**
     * true if the the user may not be banned
     * @var int
     */
    protected $userIsNotBanned = 0;

    /**
     * true if the the user has confirmed their email address
     *
     * @var int
     */
    protected $userIsEmailConfirmed = 0;

    /**
     * true if the the user has not confirmed their email address
     * @var int
     */
    protected $userIsNotEmailConfirmed = 0;

    #[\Override]
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        if (isset($conditionData['userIsBanned'])) {
            $objectList->getConditionBuilder()->add('user_table.banned = ?', [$conditionData['userIsBanned']]);
        }

        if (isset($conditionData['userIsEnabled'])) {
            if ($conditionData['userIsEnabled'] !== 0) {
                $objectList->getConditionBuilder()->add('user_table.activationCode = ?', [0]);
            } else {
                $objectList->getConditionBuilder()->add('user_table.activationCode <> ?', [0]);
            }
        }

        if (isset($conditionData['userIsEmailConfirmed'])) {
            if ($conditionData['userIsEmailConfirmed'] !== 0) {
                $objectList->getConditionBuilder()->add('user_table.emailConfirmed IS NULL');
            } else {
                $objectList->getConditionBuilder()->add('user_table.emailConfirmed IS NOT NULL');
            }
        }
    }

    #[\Override]
    public function checkUser(Condition $condition, User $user)
    {
        $userIsBanned = $condition->userIsBanned;
        if ($userIsBanned !== null && $user->banned !== $userIsBanned) {
            return false;
        }

        $userIsEnabled = $condition->userIsEnabled;
        if ($userIsEnabled !== null) {
            if ($userIsEnabled !== 0 && $user->pendingActivation()) {
                return false;
            } elseif ($userIsEnabled === 0 && !$user->pendingActivation()) {
                return false;
            }
        }

        $userIsEmailConfirmed = $condition->userIsEmailConfirmed;
        if ($userIsEmailConfirmed !== null) {
            if ($userIsEmailConfirmed !== 0 && !$user->isEmailConfirmed()) {
                return false;
            } elseif ($userIsEmailConfirmed === 0 && $user->isEmailConfirmed()) {
                return false;
            }
        }

        return true;
    }

    #[\Override]
    public function getData()
    {
        $data = [];

        if ($this->userIsBanned !== 0) {
            $data['userIsBanned'] = 1;
        } elseif ($this->userIsNotBanned !== 0) {
            $data['userIsBanned'] = 0;
        }
        if ($this->userIsEnabled !== 0) {
            $data['userIsEnabled'] = 1;
        } elseif ($this->userIsDisabled !== 0) {
            $data['userIsEnabled'] = 0;
        }
        if ($this->userIsEmailConfirmed !== 0) {
            $data['userIsEmailConfirmed'] = 1;
        } elseif ($this->userIsNotEmailConfirmed !== 0) {
            $data['userIsEmailConfirmed'] = 0;
        }

        if (!empty($data)) {
            return $data;
        }

        return null;
    }

    /**
     * Returns the "checked" attribute for an input element.
     *
     * @return  string
     */
    protected function getCheckedAttribute(string $propertyName)
    {
        if ($this->{$propertyName}) {
            return ' checked';
        }

        return '';
    }

    #[\Override]
    protected function getFieldElement()
    {
        $userIsNotBanned = WCF::getLanguage()->get('wcf.user.condition.state.isNotBanned');
        $userIsBanned = WCF::getLanguage()->get('wcf.user.condition.state.isBanned');
        $userIsDisabled = WCF::getLanguage()->get('wcf.user.condition.state.isDisabled');
        $userIsEnabled = WCF::getLanguage()->get('wcf.user.condition.state.isEnabled');
        $userIsEmailConfirmed = WCF::getLanguage()->get('wcf.user.condition.state.isEmailConfirmed');
        $userIsNotEmailConfirmed = WCF::getLanguage()->get('wcf.user.condition.state.isNotEmailConfirmed');

        return <<<HTML
<label><input type="checkbox" name="userIsBanned" value="1"{$this->getCheckedAttribute('userIsBanned')}> {$userIsBanned}</label>
<label><input type="checkbox" name="userIsNotBanned" value="1"{$this->getCheckedAttribute('userIsNotBanned')}> {$userIsNotBanned}</label>
<label><input type="checkbox" name="userIsEnabled" value="1"{$this->getCheckedAttribute('userIsEnabled')}> {$userIsEnabled}</label>
<label><input type="checkbox" name="userIsDisabled" value="1"{$this->getCheckedAttribute('userIsDisabled')}> {$userIsDisabled}</label>
<label><input type="checkbox" name="userIsEmailConfirmed" value="1"{$this->getCheckedAttribute('userIsEmailConfirmed')}> {$userIsEmailConfirmed}</label>
<label><input type="checkbox" name="userIsNotEmailConfirmed" value="1"{$this->getCheckedAttribute('userIsNotEmailConfirmed')}> {$userIsNotEmailConfirmed}</label>
HTML;
    }

    #[\Override]
    public function readFormParameters()
    {
        if (isset($_POST['userIsBanned'])) {
            $this->userIsBanned = 1;
        }
        if (isset($_POST['userIsDisabled'])) {
            $this->userIsDisabled = 1;
        }
        if (isset($_POST['userIsEnabled'])) {
            $this->userIsEnabled = 1;
        }
        if (isset($_POST['userIsNotBanned'])) {
            $this->userIsNotBanned = 1;
        }
        if (isset($_POST['userIsEmailConfirmed'])) {
            $this->userIsEmailConfirmed = 1;
        }
        if (isset($_POST['userIsNotEmailConfirmed'])) {
            $this->userIsNotEmailConfirmed = 1;
        }
    }

    #[\Override]
    public function reset()
    {
        $this->userIsBanned = 0;
        $this->userIsDisabled = 0;
        $this->userIsEnabled = 0;
        $this->userIsNotBanned = 0;
        $this->userIsEmailConfirmed = 0;
        $this->userIsNotEmailConfirmed = 0;
    }

    #[\Override]
    public function setData(Condition $condition)
    {
        /** @var ?int $userIsBanned */
        $userIsBanned = $condition->userIsBanned;
        if ($userIsBanned !== null) {
            $this->userIsBanned = $userIsBanned;
            $this->userIsNotBanned = $userIsBanned !== 0 ? 0 : 1;
        }

        /** @var ?int $userIsEnabled */
        $userIsEnabled = $condition->userIsEnabled;
        if ($userIsEnabled !== null) {
            $this->userIsEnabled = $userIsEnabled;
            $this->userIsDisabled = $userIsEnabled !== 0 ? 0 : 1;
        }

        /** @var ?int $userIsEmailConfirmed */
        $userIsEmailConfirmed = $condition->userIsEmailConfirmed;
        if ($userIsEmailConfirmed !== null) {
            $this->userIsEmailConfirmed = $userIsEmailConfirmed;
            $this->userIsNotEmailConfirmed = $userIsEmailConfirmed !== 0 ? 0 : 1;
        }
    }

    #[\Override]
    public function validate()
    {
        if ($this->userIsBanned !== 0 && $this->userIsNotBanned !== 0) {
            $this->errorMessage = 'wcf.user.condition.state.isBanned.error.conflict';

            throw new UserInputException('userIsBanned', 'conflict');
        }

        if ($this->userIsDisabled !== 0 && $this->userIsEnabled !== 0) {
            $this->errorMessage = 'wcf.user.condition.state.isEnabled.error.conflict';

            throw new UserInputException('userIsEnabled', 'conflict');
        }

        if ($this->userIsEmailConfirmed !== 0 && $this->userIsNotEmailConfirmed !== 0) {
            $this->errorMessage = 'wcf.user.condition.state.isEmailConfirmed.error.conflict';

            throw new UserInputException('userIsEmailConfirmed', 'conflict');
        }
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
