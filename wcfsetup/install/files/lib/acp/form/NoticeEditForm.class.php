<?php

namespace wcf\acp\form;

use wcf\data\notice\Notice;
use wcf\data\notice\NoticeAction;
use wcf\form\AbstractForm;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Shows the form to edit an existing notice.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class NoticeEditForm extends NoticeAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.notice.list';

    /**
     * edited notice object
     * @var Notice
     */
    public $notice;

    /**
     * id of the edited notice object
     * @var int
     */
    public $noticeID = 0;

    /**
     * 1 if the notice will be displayed for all users again
     * @var int
     */
    public $resetIsDismissed = 0;

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        I18nHandler::getInstance()->assignVariables(!empty($_POST));

        WCF::getTPL()->assign([
            'action' => 'edit',
            'notice' => $this->notice,
            'resetIsDismissed' => $this->resetIsDismissed,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (empty($_POST)) {
            I18nHandler::getInstance()->setOptions('notice', 1, $this->notice->notice, 'wcf.notice.notice.notice\d+');

            $this->cssClassName = $this->notice->cssClassName;
            if (!\in_array($this->cssClassName, Notice::TYPES)) {
                $this->customCssClassName = $this->cssClassName;
                $this->cssClassName = 'custom';
            }

            $this->isDisabled = $this->notice->isDisabled;
            $this->isDismissible = $this->notice->isDismissible;
            $this->noticeName = $this->notice->noticeName;
            $this->noticeUseHtml = $this->notice->noticeUseHtml;
            $this->showOrder = $this->notice->showOrder;

            $conditions = $this->notice->getConditions();
            $conditionsByObjectTypeID = [];
            foreach ($conditions as $condition) {
                $conditionsByObjectTypeID[$condition->objectTypeID] = $condition;
            }

            foreach ($this->groupedConditionObjectTypes as $objectTypes1) {
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
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['resetIsDismissed'])) {
            $this->resetIsDismissed = 1;
        }
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->noticeID = \intval($_REQUEST['id']);
        }
        $this->notice = new Notice($this->noticeID);
        if (!$this->notice->noticeID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        $this->objectAction = new NoticeAction([$this->notice], 'update', [
            'data' => \array_merge($this->additionalFields, [
                'cssClassName' => $this->cssClassName == 'custom' ? $this->customCssClassName : $this->cssClassName,
                'isDisabled' => $this->isDisabled,
                'isDismissible' => $this->isDismissible,
                'notice' => I18nHandler::getInstance()->isPlainValue('notice') ? I18nHandler::getInstance()->getValue('notice') : 'wcf.notice.notice.notice' . $this->notice->noticeID,
                'noticeName' => $this->noticeName,
                'noticeUseHtml' => $this->noticeUseHtml,
                'showOrder' => $this->showOrder,
            ]),
        ]);
        $this->objectAction->executeAction();

        if (I18nHandler::getInstance()->isPlainValue('notice')) {
            if ($this->notice->notice == 'wcf.notice.notice.notice' . $this->notice->noticeID) {
                I18nHandler::getInstance()->remove($this->notice->notice);
            }
        } else {
            I18nHandler::getInstance()->save(
                'notice',
                'wcf.notice.notice.notice' . $this->notice->noticeID,
                'wcf.notice',
                1
            );
        }

        // transform conditions array into one-dimensional array
        $conditions = [];
        foreach ($this->groupedConditionObjectTypes as $groupedObjectTypes) {
            foreach ($groupedObjectTypes as $objectTypes) {
                if (\is_array($objectTypes)) {
                    $conditions = \array_merge($conditions, $objectTypes);
                } else {
                    $conditions[] = $objectTypes;
                }
            }
        }

        ConditionHandler::getInstance()->updateConditions(
            $this->notice->noticeID,
            $this->notice->getConditions(),
            $conditions
        );

        if ($this->resetIsDismissed) {
            $sql = "DELETE FROM wcf1_notice_dismissed
                    WHERE       noticeID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $this->notice->noticeID,
            ]);

            $this->resetIsDismissed = 0;

            UserStorageHandler::getInstance()->resetAll('dismissedNotices');
        }

        $this->saved();

        // reload notice object for proper 'isDismissible' value
        $this->notice = new Notice($this->noticeID);

        WCF::getTPL()->assign('success', true);
    }
}
