<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\data\DatabaseObjectList;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyList;
use wcf\data\user\trophy\UserTrophyList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\exception\InvalidObjectArgument;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Condition implementation for trophies.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserTrophyCondition extends AbstractMultipleFieldsCondition implements
    IContentCondition,
    IObjectListCondition,
    IUserCondition
{
    use TObjectListUserCondition;

    /**
     * @inheritDoc
     */
    protected $descriptions = [
        'userTrophyIDs' => 'wcf.user.condition.userTrophyIDs.description',
        'notUserTrophyIDs' => 'wcf.user.condition.notUserTrophyIDs.description',
    ];

    /**
     * @inheritDoc
     */
    protected $labels = [
        'userTrophyIDs' => 'wcf.user.condition.userTrophyIDs',
        'notUserTrophyIDs' => 'wcf.user.condition.notUserTrophyIDs',
    ];

    /**
     * ids of the selected trophies the user has earned
     * @var int[]
     */
    protected $userTrophyIDs = [];

    /**
     * ids of the selected trophies the user has not earned
     * @var int[]
     */
    protected $notUserTrophyIDs = [];

    /**
     * selectable trophies
     * @var Trophy[]
     */
    protected $trophies;

    /**
     * @inheritDoc
     */
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        if (!($objectList instanceof UserList)) {
            throw new InvalidObjectArgument($objectList, UserList::class, 'Object list');
        }

        if (isset($conditionData['userTrophyIDs'])) {
            $objectList->getConditionBuilder()->add(
                'user_table.userID IN (
                    SELECT      userID
                    FROM        wcf1_user_trophy
                    WHERE       trophyID IN (?)
                    GROUP BY    userID
                    HAVING      COUNT(DISTINCT trophyID) = ?
                )',
                [$conditionData['userTrophyIDs'], \count($conditionData['userTrophyIDs'])]
            );
        }
        if (isset($conditionData['notUserTrophyIDs'])) {
            $objectList->getConditionBuilder()->add(
                'user_table.userID NOT IN (
                    SELECT  userID
                    FROM    wcf1_user_trophy
                    WHERE   trophyID IN (?)
                )',
                [$conditionData['notUserTrophyIDs']]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function checkUser(Condition $condition, User $user)
    {
        $userTrophies = UserTrophyList::getUserTrophies([$user->getObjectID()], false)[$user->getObjectID()];
        $trophyIDs = \array_map(static function ($userTrophy) {
            return $userTrophy->trophyID;
        }, $userTrophies);

        if (
            !empty($condition->conditionData['userTrophyIDs'])
            && !empty(\array_diff($condition->conditionData['userTrophyIDs'], $trophyIDs))
        ) {
            return false;
        }

        if (
            !empty($condition->conditionData['notUserTrophyIDs'])
            && !empty(\array_intersect($condition->conditionData['notUserTrophyIDs'], $trophyIDs))
        ) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        $data = [];

        if (!empty($this->userTrophyIDs)) {
            $data['userTrophyIDs'] = $this->userTrophyIDs;
        }
        if (!empty($this->notUserTrophyIDs)) {
            $data['notUserTrophyIDs'] = $this->notUserTrophyIDs;
        }

        if (!empty($data)) {
            return $data;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getHTML()
    {
        if (!\count($this->getTrophies())) {
            return '';
        }

        return <<<HTML
<dl{$this->getErrorClass('userTrophyIDs')}>
	<dt>{$this->getLabel('userTrophyIDs')}</dt>
	<dd>
		{$this->getOptionElements('userTrophyIDs')}
		{$this->getDescriptionElement('userTrophyIDs')}
		{$this->getErrorMessageElement('userTrophyIDs')}
	</dd>
</dl>
<dl{$this->getErrorClass('notUserTrophyIDs')}>
	<dt>{$this->getLabel('notUserTrophyIDs')}</dt>
	<dd>
		{$this->getOptionElements('notUserTrophyIDs')}
		{$this->getDescriptionElement('notUserTrophyIDs')}
		{$this->getErrorMessageElement('notUserTrophyIDs')}
	</dd>
</dl>
HTML;
    }

    /**
     * Returns the option elements for the user group selection.
     *
     * @param string $identifier
     * @return  string
     */
    protected function getOptionElements($identifier)
    {
        $trophies = $this->getTrophies();

        $returnValue = "";
        foreach ($trophies as $trophy) {
            /** @noinspection PhpVariableVariableInspection */
            $returnValue .= "<label><input type=\"checkbox\" name=\"" . $identifier . "[]\" value=\"" . $trophy->trophyID . "\"" . (\in_array(
                $trophy->trophyID,
                $this->{$identifier}
            ) ? ' checked' : "") . "> " . StringUtil::encodeHTML($trophy->getTitle()) . "</label>";
        }

        return $returnValue;
    }

    /**
     * Returns the selectable user groups.
     *
     * @return  Trophy[]
     */
    protected function getTrophies()
    {
        if ($this->trophies == null) {
            $trophyList = new TrophyList();
            $trophyList->readObjects();
            $this->trophies = $trophyList->getObjects();

            $collator = new \Collator(WCF::getLanguage()->getLocale());
            \uasort(
                $this->trophies,
                static fn (Trophy $a, Trophy $b) => $collator->compare($a->getTitle(), $b->getTitle())
            );
        }

        return $this->trophies;
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        if (isset($_POST['userTrophyIDs'])) {
            $this->userTrophyIDs = ArrayUtil::toIntegerArray($_POST['userTrophyIDs']);
        }
        if (isset($_POST['notUserTrophyIDs'])) {
            $this->notUserTrophyIDs = ArrayUtil::toIntegerArray($_POST['notUserTrophyIDs']);
        }
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        $this->userTrophyIDs = [];
        $this->notUserTrophyIDs = [];
    }

    /**
     * @inheritDoc
     */
    public function setData(Condition $condition)
    {
        if ($condition->userTrophyIDs !== null) {
            $this->userTrophyIDs = $condition->userTrophyIDs;
        }
        if ($condition->notUserTrophyIDs !== null) {
            $this->notUserTrophyIDs = $condition->notUserTrophyIDs;
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $trophies = $this->getTrophies();
        foreach ($this->userTrophyIDs as $trophyID) {
            if (!isset($trophies[$trophyID])) {
                $this->errorMessages['userTrophyIDs'] = 'wcf.global.form.error.noValidSelection';

                throw new UserInputException('userTrophyIDs', 'noValidSelection');
            }
        }
        foreach ($this->notUserTrophyIDs as $trophyID) {
            if (!isset($trophies[$trophyID])) {
                $this->errorMessages['notUserTrophyIDs'] = 'wcf.global.form.error.noValidSelection';

                throw new UserInputException('notUserTrophyIDs', 'noValidSelection');
            }
        }

        if (\count(\array_intersect($this->notUserTrophyIDs, $this->userTrophyIDs))) {
            $this->errorMessages['notUserTrophyIDs'] = 'wcf.user.condition.notUserTrophyIDs.error.userTrophyIntersection';

            throw new UserInputException('notUserTrophyIDs', 'userTrophyIntersection');
        }
    }

    /**
     * @inheritDoc
     */
    public function showContent(Condition $condition)
    {
        return $this->checkUser($condition, WCF::getUser());
    }
}
