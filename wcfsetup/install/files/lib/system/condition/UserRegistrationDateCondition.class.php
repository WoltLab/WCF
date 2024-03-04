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
 * Condition implementation for the registration date of a user.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserRegistrationDateCondition extends AbstractSingleFieldCondition implements
    IContentCondition,
    IObjectListCondition,
    IUserCondition
{
    use TObjectListUserCondition;

    /**
     * @inheritDoc
     */
    protected $label = 'wcf.user.condition.registrationDate';

    /**
     * registration start date
     * @var string
     */
    protected $registrationDateEnd = '';

    /**
     * registration start date
     * @var string
     */
    protected $registrationDateStart = '';

    /**
     * @inheritDoc
     */
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        if (!($objectList instanceof UserList)) {
            throw new InvalidObjectArgument($objectList, UserList::class, 'Object list');
        }

        if (isset($conditionData['registrationDateEnd'])) {
            $registrationDateEnd = \DateTime::createFromFormat(
                'Y-m-d',
                $conditionData['registrationDateEnd'],
                TIMEZONE
            );
            $registrationDateEnd->setTime(23, 59, 59);
            $objectList->getConditionBuilder()->add(
                'user_table.registrationDate < ?',
                [$registrationDateEnd->getTimestamp()]
            );
        }
        if (isset($conditionData['registrationDateStart'])) {
            $registrationDateStart = \DateTime::createFromFormat(
                'Y-m-d',
                $conditionData['registrationDateStart'],
                TIMEZONE
            );
            $registrationDateStart->setTime(0, 0, 0);
            $objectList->getConditionBuilder()->add(
                'user_table.registrationDate >= ?',
                [$registrationDateStart->getTimestamp()]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function checkUser(Condition $condition, User $user)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $registrationDateStart = \DateTime::createFromFormat('Y-m-d', $condition->registrationDateStart, TIMEZONE);
        if ($registrationDateStart !== false) {
            $registrationDateStart->setTime(0, 0, 0);
            if ($user->registrationDate < $registrationDateStart->getTimestamp()) {
                return false;
            }
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $registrationDateEnd = \DateTime::createFromFormat('Y-m-d', $condition->registrationDateEnd, TIMEZONE);
        if ($registrationDateEnd !== false) {
            $registrationDateEnd->setTime(23, 59, 59);
            if ($user->registrationDate >= $registrationDateEnd->getTimestamp()) {
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

        if (\strlen($this->registrationDateStart)) {
            $data['registrationDateStart'] = $this->registrationDateStart;
        }
        if (\strlen($this->registrationDateEnd)) {
            $data['registrationDateEnd'] = $this->registrationDateEnd;
        }

        if (!empty($data)) {
            return $data;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function getFieldElement()
    {
        $start = WCF::getLanguage()->get('wcf.date.period.start');
        $end = WCF::getLanguage()->get('wcf.date.period.end');

        return <<<HTML
<input type="date" id="registrationDateStart" name="registrationDateStart" value="{$this->registrationDateStart}" placeholder="{$start}">
<input type="date" id="registrationDateEnd" name="registrationDateEnd" value="{$this->registrationDateEnd}" placeholder="{$end}">
HTML;
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        if (isset($_POST['registrationDateEnd'])) {
            $this->registrationDateEnd = $_POST['registrationDateEnd'];
        }
        if (isset($_POST['registrationDateStart'])) {
            $this->registrationDateStart = $_POST['registrationDateStart'];
        }
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        $this->registrationDateEnd = '';
        $this->registrationDateStart = '';
    }

    /**
     * @inheritDoc
     */
    public function setData(Condition $condition)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $registrationDateEnd = $condition->registrationDateEnd;
        if ($registrationDateEnd) {
            $this->registrationDateEnd = $registrationDateEnd;
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $registrationDateStart = $condition->registrationDateStart;
        if ($registrationDateStart) {
            $this->registrationDateStart = $registrationDateStart;
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $registrationDateEnd = $registrationDateStart = null;
        if (\strlen($this->registrationDateStart)) {
            $registrationDateStart = \DateTime::createFromFormat('Y-m-d', $this->registrationDateStart, TIMEZONE);
            if ($registrationDateStart === false) {
                $this->errorMessage = 'wcf.condition.timestamp.error.invalidStart';

                throw new UserInputException('registrationDate', 'invalidStart');
            }
        }
        if (\strlen($this->registrationDateEnd)) {
            $registrationDateEnd = \DateTime::createFromFormat('Y-m-d', $this->registrationDateEnd, TIMEZONE);
            if ($registrationDateEnd === false) {
                $this->errorMessage = 'wcf.condition.timestamp.error.invalidEnd';

                throw new UserInputException('registrationDate', 'invalidEnd');
            }
        }

        if (
            $registrationDateEnd !== null
            && $registrationDateStart !== null
            && $registrationDateEnd < $registrationDateStart
        ) {
            $this->errorMessage = 'wcf.condition.timestamp.error.endBeforeStart';

            throw new UserInputException('registrationDate', 'endBeforeStart');
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
