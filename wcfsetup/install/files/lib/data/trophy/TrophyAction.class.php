<?php

namespace wcf\data\trophy;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\file\FileEditor;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;
use wcf\data\TI18nDatabaseObjectAction;
use wcf\data\user\trophy\UserTrophyAction;
use wcf\data\user\trophy\UserTrophyList;
use wcf\data\user\UserAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\upload\TrophyImageUploadFileValidationStrategy;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Trophy related actions.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @extends AbstractDatabaseObjectAction<Trophy, TrophyEditor>
 */
class TrophyAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    use TDatabaseObjectToggle;
    use TI18nDatabaseObjectAction;

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.trophy.canManageTrophy'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.trophy.canManageTrophy'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['toggle', 'delete'];

    /**
     * @inheritDoc
     */
    public function create()
    {
        $showOrder = 0;
        if (isset($this->parameters['data']['showOrder'])) {
            $showOrder = $this->parameters['data']['showOrder'];
            unset($this->parameters['data']['showOrder']);
        }

        $trophy = parent::create();

        $this->saveI18nValue($trophy);

        $trophyEditor = new TrophyEditor($trophy);
        $trophyEditor->setShowOrder($showOrder);

        return new Trophy($trophy->trophyID);
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        // update trophy points
        $userTrophyList = new UserTrophyList();
        if (!empty($userTrophyList->sqlJoins)) {
            $userTrophyList->sqlJoins .= ' ';
        }
        $userTrophyList->sqlJoins .= '
            LEFT JOIN   wcf1_trophy trophy
            ON          user_trophy.trophyID = trophy.trophyID
            LEFT JOIN   wcf1_category category
            ON          trophy.categoryID = category.categoryID';

        $userTrophyList->getConditionBuilder()->add('trophy.isDisabled = ?', [0]);
        $userTrophyList->getConditionBuilder()->add('category.isDisabled = ?', [0]);
        $userTrophyList->getConditionBuilder()->add('user_trophy.trophyID IN (?)', [$this->getObjectIDs()]);
        $userTrophyList->readObjects();

        $userTrophyAction = new UserTrophyAction($userTrophyList->getObjects(), 'delete');
        $userTrophyAction->executeAction();

        $fileIDs = [];
        foreach ($this->getObjects() as $trophy) {
            if ($trophy->imageFileID) {
                $fileIDs[] = $trophy->imageFileID;
            }
        }

        if ($fileIDs !== []) {
            FileEditor::deleteAll($fileIDs);
        }

        $returnValues = parent::delete();

        $this->deleteI18nValues();

        UserStorageHandler::getInstance()->resetAll('specialTrophies');

        return $returnValues;
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        parent::update();

        if (\count($this->objects) == 1 && isset($this->parameters['data']['showOrder']) && $this->parameters['data']['showOrder'] != \reset($this->objects)->showOrder) {
            \reset($this->objects)->setShowOrder($this->parameters['data']['showOrder']);
        }

        foreach ($this->objects as $object) {
            $this->saveI18nValue($object->getDecoratedObject());
        }
    }

    /**
     * @inheritDoc
     */
    public function toggle()
    {
        $enabledTrophyIDs = [];
        $disabledTrophyIDs = [];

        foreach ($this->getObjects() as $trophy) {
            $trophy->update(['isDisabled' => $trophy->isDisabled ? 0 : 1]);

            if (!$trophy->isDisabled) {
                $disabledTrophyIDs[] = $trophy->trophyID;
            } else {
                $enabledTrophyIDs[] = $trophy->trophyID;
            }
        }

        if (!empty($disabledTrophyIDs)) {
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('trophyID IN (?)', [$disabledTrophyIDs]);
            $sql = "DELETE FROM wcf1_user_special_trophy
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());

            // update trophy points
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('trophyID IN (?)', [$disabledTrophyIDs]);
            $sql = "SELECT      COUNT(*) as count, userID
                    FROM        wcf1_user_trophy
                    " . $conditionBuilder . "
                    GROUP BY    userID";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());

            while ($row = $statement->fetchArray()) {
                $userAction = new UserAction([$row['userID']], 'update', [
                    'counters' => [
                        'trophyPoints' => $row['count'] * -1,
                    ],
                ]);
                $userAction->executeAction();
            }
        }

        if (!empty($enabledTrophyIDs)) {
            // update trophy points
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('trophyID IN (?)', [$enabledTrophyIDs]);
            $sql = "SELECT      COUNT(*) as count, userID
                    FROM        wcf1_user_trophy
                    " . $conditionBuilder . "
                    GROUP BY    userID";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());

            while ($row = $statement->fetchArray()) {
                $userAction = new UserAction([$row['userID']], 'update', [
                    'counters' => [
                        'trophyPoints' => $row['count'],
                    ],
                ]);
                $userAction->executeAction();
            }
        }

        UserStorageHandler::getInstance()->resetAll('specialTrophies');
    }

    #[\Override]
    public function getI18nSaveTypes(): array
    {
        return [
            'title' => 'wcf.user.trophy.title\d+',
            'description' => 'wcf.user.trophy.description\d+',
        ];
    }

    #[\Override]
    public function getLanguageCategory(): string
    {
        return 'wcf.user.trophy';
    }

    #[\Override]
    public function getPackageID(): int
    {
        return 1;
    }
}
