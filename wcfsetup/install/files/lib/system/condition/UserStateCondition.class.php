<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\exception\InvalidObjectArgument;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Condition implementation for the state (banned, enabled) of a user.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Condition
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

    /**
     * @inheritDoc
     */
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        if (!($objectList instanceof UserList)) {
            throw new InvalidObjectArgument($objectList, UserList::class, 'Object list');
        }

        if (isset($conditionData['userIsBanned'])) {
            $objectList->getConditionBuilder()->add('user_table.banned = ?', [$conditionData['userIsBanned']]);
        }

        if (isset($conditionData['userIsEnabled'])) {
            if ($conditionData['userIsEnabled']) {
                $objectList->getConditionBuilder()->add('user_table.activationCode = ?', [0]);
            } else {
                $objectList->getConditionBuilder()->add('user_table.activationCode <> ?', [0]);
            }
        }

        if (isset($conditionData['userIsEmailConfirmed'])) {
            if ($conditionData['userIsEmailConfirmed']) {
                $objectList->getConditionBuilder()->add('user_table.emailConfirmed IS NULL');
            } else {
                $objectList->getConditionBuilder()->add('user_table.emailConfirmed IS NOT NULL');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function checkUser(Condition $condition, User $user)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $userIsBanned = $condition->userIsBanned;
        if ($userIsBanned !== null && $user->banned != $userIsBanned) {
            return false;
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $userIsEnabled = $condition->userIsEnabled;
        if ($userIsEnabled !== null) {
            if ($userIsEnabled && $user->pendingActivation()) {
                return false;
            } elseif (!$userIsEnabled && !$user->pendingActivation()) {
                return false;
            }
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $userIsEmailConfirmed = $condition->userIsEmailConfirmed;
        if ($userIsEmailConfirmed !== null) {
            if ($userIsEmailConfirmed && !$user->isEmailConfirmed()) {
                return false;
            } elseif (!$userIsEmailConfirmed && $user->isEmailConfirmed()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        $data = [];

        if ($this->userIsBanned) {
            $data['userIsBanned'] = 1;
        } elseif ($this->userIsNotBanned) {
            $data['userIsBanned'] = 0;
        }
        if ($this->userIsEnabled) {
            $data['userIsEnabled'] = 1;
        } elseif ($this->userIsDisabled) {
            $data['userIsEnabled'] = 0;
        }
        if ($this->userIsEmailConfirmed) {
            $data['userIsEmailConfirmed'] = 1;
        } elseif ($this->userIsNotEmailConfirmed) {
            $data['userIsEmailConfirmed'] = 0;
        }

        if (!empty($data)) {
            return $data;
        }
    }

    /**
     * Returns the "checked" attribute for an input element.
     *
     * @param   string      $propertyName
     * @return  string
     */
    protected function getCheckedAttribute($propertyName)
    {
        /** @noinspection PhpVariableVariableInspection */
        if ($this->{$propertyName}) {
            return ' checked';
        }

        return '';
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function reset()
    {
        $this->userIsBanned = 0;
        $this->userIsDisabled = 0;
        $this->userIsEnabled = 0;
        $this->userIsNotBanned = 0;
        $this->userIsEmailConfirmed = 0;
        $this->userIsNotEmailConfirmed = 0;
    }

    /**
     * @inheritDoc
     */
    public function setData(Condition $condition)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $userIsBanned = $condition->userIsBanned;
        if ($condition->userIsBanned !== null) {
            $this->userIsBanned = $userIsBanned;
            $this->userIsNotBanned = !$userIsBanned;
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $userIsEnabled = $condition->userIsEnabled;
        if ($condition->userIsEnabled !== null) {
            $this->userIsEnabled = $userIsEnabled;
            $this->userIsDisabled = !$userIsEnabled;
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $userIsEmailConfirmed = $condition->userIsEmailConfirmed;
        if ($condition->userIsEmailConfirmed !== null) {
            $this->userIsEmailConfirmed = $userIsEmailConfirmed;
            $this->userIsNotEmailConfirmed = !$userIsEmailConfirmed;
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if ($this->userIsBanned && $this->userIsNotBanned) {
            $this->errorMessage = 'wcf.user.condition.state.isBanned.error.conflict';

            throw new UserInputException('userIsBanned', 'conflict');
        }

        if ($this->userIsDisabled && $this->userIsEnabled) {
            $this->errorMessage = 'wcf.user.condition.state.isEnabled.error.conflict';

            throw new UserInputException('userIsEnabled', 'conflict');
        }

        if ($this->userIsEmailConfirmed && $this->userIsNotEmailConfirmed) {
            $this->errorMessage = 'wcf.user.condition.state.isEmailConfirmed.error.conflict';

            throw new UserInputException('userIsEmailConfirmed', 'conflict');
        }
    }

    /**
     * @inheritDoc
     */
    public function showContent(Condition $condition)
    {
        if (!WCF::getUser()->userID) {
            return false;
        }

        return $this->checkUser($condition, WCF::getUser());
    }
}
