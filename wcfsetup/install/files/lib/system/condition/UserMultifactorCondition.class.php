<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Condition implementation for the multi-factor status of users.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.4
 *
 * @implements IObjectListCondition<UserList>
 */
class UserMultifactorCondition extends AbstractSingleFieldCondition implements
    IContentCondition,
    IObjectListCondition,
    IUserCondition
{
    use TObjectListUserCondition;

    /**
     * @inheritDoc
     */
    protected $label = 'wcf.user.condition.multifactor';

    /**
     * 1 if multifactor active checkbox is checked
     * @var int
     */
    protected $multifactorActive = 0;

    /**
     * 1 if multifactor not active checkbox is checked
     * @var int
     */
    protected $multifactorNotActive = 0;

    #[\Override]
    public function getData()
    {
        if ($this->multifactorActive || $this->multifactorNotActive) {
            return [
                // if multifactorNotActive is selected multifactorActive is 0
                // otherwise multifactorNotActive is 1
                'multifactorActive' => $this->multifactorActive,
            ];
        }

        return null;
    }

    #[\Override]
    public function getFieldElement()
    {
        $multifactorActiveLabel = WCF::getLanguage()->get('wcf.user.condition.multifactor.multifactorActive');
        $multifactorNotActiveLabel = WCF::getLanguage()->get('wcf.user.condition.multifactor.multifactorNotActive');
        $multifactorActiveChecked = '';
        if ($this->multifactorActive) {
            $multifactorActiveChecked = ' checked';
        }

        $multifactorNotActiveChecked = '';
        if ($this->multifactorNotActive) {
            $multifactorNotActiveChecked = ' checked';
        }

        return <<<HTML
<label><input type="checkbox" name="multifactorActive" id="multifactorActive"{$multifactorActiveChecked}> {$multifactorActiveLabel}</label>
<label><input type="checkbox" name="multifactorNotActive" id="multifactorNotActive"{$multifactorNotActiveChecked}> {$multifactorNotActiveLabel}</label>
HTML;
    }

    #[\Override]
    public function readFormParameters()
    {
        if (isset($_POST['multifactorActive'])) {
            $this->multifactorActive = 1;
        }
        if (isset($_POST['multifactorNotActive'])) {
            $this->multifactorNotActive = 1;
        }
    }

    #[\Override]
    public function reset()
    {
        $this->multifactorActive = $this->multifactorNotActive = 0;
    }

    #[\Override]
    public function setData(Condition $condition)
    {
        $this->multifactorActive = $condition->multifactorActive;
        $this->multifactorNotActive = $condition->multifactorActive ? 0 : 1;
    }

    #[\Override]
    public function validate()
    {
        if ($this->multifactorActive && $this->multifactorNotActive) {
            $this->errorMessage = 'wcf.user.condition.multifactor.multifactorActive.error.conflict';

            throw new UserInputException('multifactorActive', 'conflict');
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

    #[\Override]
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        if (isset($conditionData['multifactorActive'])) {
            $objectList->getConditionBuilder()->add(
                'user_table.multifactorActive = ?',
                [$conditionData['multifactorActive']]
            );
        }
    }

    #[\Override]
    public function checkUser(Condition $condition, User $user)
    {
        if ($condition->multifactorActive !== null && $user->multifactorActive !== $condition->multifactorActive) {
            return false;
        }

        return true;
    }
}
