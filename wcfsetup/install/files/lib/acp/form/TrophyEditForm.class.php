<?php

namespace wcf\acp\form;

use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyAction;
use wcf\data\user\UserAction;
use wcf\system\condition\ConditionHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\trophy\condition\TrophyConditionHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Represents the trophy edit form.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
class TrophyEditForm extends TrophyAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.trophy.list';

    /**
     * @inheritDoc
     */
    public $action = 'edit';

    /**
     * trophy id
     * @var int
     */
    public $trophyID = 0;

    /**
     * trophy object
     * @var Trophy
     */
    public $trophy;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        if (!empty($_REQUEST['id'])) {
            $this->trophyID = \intval($_REQUEST['id']);
        }
        $this->trophy = new Trophy($this->trophyID);

        if (!$this->trophy->trophyID) {
            throw new IllegalLinkException();
        }

        parent::readParameters();
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (empty($_POST)) {
            $this->readDataI18n($this->trophy);

            $this->categoryID = $this->trophy->categoryID;
            $this->type = $this->trophy->type;
            $this->isDisabled = $this->trophy->isDisabled;
            $this->iconName = $this->trophy->iconName;
            $this->iconColor = $this->trophy->iconColor;
            $this->badgeColor = $this->trophy->badgeColor;
            $this->awardAutomatically = $this->trophy->awardAutomatically;
            $this->revokeAutomatically = $this->trophy->revokeAutomatically;
            $this->trophyUseHtml = $this->trophy->trophyUseHtml;
            $this->showOrder = $this->trophy->showOrder;

            // reset badge values for non badge trophies
            if ($this->trophy->type != Trophy::TYPE_BADGE) {
                $this->iconName = 'trophy;false';
                $this->iconColor = 'rgba(255, 255, 255, 1)';
                $this->badgeColor = 'rgba(50, 92, 132, 1)';
            }

            $conditions = $this->trophy->getConditions();
            $conditionsByObjectTypeID = [];
            foreach ($conditions as $condition) {
                $conditionsByObjectTypeID[$condition->objectTypeID] = $condition;
            }

            foreach ($this->conditions as $objectTypes1) {
                foreach ($objectTypes1 as $objectTypes2) {
                    if (\is_array($objectTypes2)) {
                        foreach ($objectTypes2 as $objectType) {
                            if (isset($conditionsByObjectTypeID[$objectType->objectTypeID])) {
                                $conditionsByObjectTypeID[$objectType->objectTypeID]->getObjectType()->getProcessor()->setData($conditionsByObjectTypeID[$objectType->objectTypeID]);
                            }
                        }
                    } elseif (isset($conditionsByObjectTypeID[$objectTypes2->objectTypeID])) {
                        $conditionsByObjectTypeID[$objectTypes2->objectTypeID]->getObjectType()->getProcessor()->setData($conditionsByObjectTypeID[$objectTypes2->objectTypeID]);
                    }
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function validateType()
    {
        switch ($this->type) {
            case Trophy::TYPE_IMAGE:
                if (empty($this->trophy->iconFile) || !\file_exists(WCF_DIR . 'images/trophy/' . $this->trophy->iconFile)) {
                    throw new UserInputException('imageUpload');
                }
                break;

            case Trophy::TYPE_BADGE:
                if (empty($this->iconName)) {
                    throw new UserInputException('iconName');
                }

                if (empty($this->iconColor)) {
                    throw new UserInputException('iconColor');
                }

                if (empty($this->badgeColor)) {
                    throw new UserInputException('badgeColor');
                }
                break;
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractAcpForm::save();

        $this->beforeSaveI18n($this->trophy);

        $data = [];
        if ($this->type == Trophy::TYPE_IMAGE) {
            $data['iconName'] = '';
            $data['iconColor'] = '';
            $data['badgeColor'] = '';
        } elseif ($this->type == Trophy::TYPE_BADGE) {
            // delete old image icon
            if (\is_file(WCF_DIR . 'images/trophy/' . $this->trophy->iconFile)) {
                @\unlink(WCF_DIR . 'images/trophy/' . $this->trophy->iconFile);
            }

            $data['iconName'] = $this->iconName;
            $data['iconColor'] = $this->iconColor;
            $data['badgeColor'] = $this->badgeColor;
            $data['iconFile'] = '';
        }

        $this->objectAction = new TrophyAction([$this->trophy], 'update', [
            'data' => \array_merge($this->additionalFields, $data, [
                'title' => $this->title,
                'description' => $this->description,
                'categoryID' => $this->categoryID,
                'type' => $this->type,
                'isDisabled' => $this->isDisabled,
                'awardAutomatically' => $this->awardAutomatically,
                'revokeAutomatically' => $this->revokeAutomatically,
                'trophyUseHtml' => $this->trophyUseHtml,
                'showOrder' => $this->showOrder,
            ]),
        ]);
        $this->objectAction->executeAction();

        // transform conditions array into one-dimensional array
        $conditions = [];
        foreach ($this->conditions as $groupedObjectTypes) {
            foreach ($groupedObjectTypes as $objectTypes) {
                if (\is_array($objectTypes)) {
                    $conditions = \array_merge($conditions, $objectTypes);
                } else {
                    $conditions[] = $objectTypes;
                }
            }
        }

        if ($this->awardAutomatically) {
            ConditionHandler::getInstance()->updateConditions(
                $this->trophy->trophyID,
                $this->trophy->getConditions(),
                $conditions
            );
        } else {
            ConditionHandler::getInstance()->deleteConditions(
                TrophyConditionHandler::CONDITION_DEFINITION_NAME,
                [$this->trophy->trophyID]
            );
        }

        // reset special trophies, if trophy is disabled
        if ($this->isDisabled) {
            $sql = "DELETE FROM wcf1_user_special_trophy
                    WHERE       trophyID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->trophyID]);

            UserStorageHandler::getInstance()->resetAll('specialTrophies');
        }

        if ($this->isDisabled != $this->trophy->isDisabled) {
            // update trophy points
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('trophyID = ?', [$this->trophyID]);
            $sql = "SELECT      COUNT(*) as count, userID
                    FROM        wcf1_user_trophy
                    " . $conditionBuilder . "
                    GROUP BY    userID";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());

            while ($row = $statement->fetchArray()) {
                $userAction = new UserAction([$row['userID']], 'update', [
                    'counters' => [
                        'trophyPoints' => $row['count'] * ($this->isDisabled) ? -1 : 1,
                    ],
                ]);
                $userAction->executeAction();
            }
        }

        $this->saved();

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        I18nHandler::getInstance()->assignVariables(!empty($_POST));

        WCF::getTPL()->assign([
            'trophy' => $this->trophy,
        ]);
    }
}
