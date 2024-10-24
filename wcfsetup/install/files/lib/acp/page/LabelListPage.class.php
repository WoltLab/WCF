<?php

namespace wcf\acp\page;

use wcf\data\label\group\LabelGroup;
use wcf\data\label\group\LabelGroupList;
use wcf\data\label\LabelList;
use wcf\data\language\item\LanguageItemList;
use wcf\page\SortablePage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Lists available labels
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    LabelList $objectList
 */
class LabelListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.label.list';

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'label';

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 50;

    /**
     * @inheritDoc
     */
    public $validSortFields = ['labelID', 'label', 'groupName', 'showOrder'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.label.canManageLabel'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = LabelList::class;

    /**
     * filter for css class name
     * @var string
     */
    public $cssClassName = '';

    /**
     * if of the label group to which the displayed labels belong
     * @var int
     */
    public $groupID = 0;

    /**
     * filter for label name
     * @var string
     */
    public $label = '';

    /**
     * label group to which the displayed labels belong
     * @var LabelGroup
     */
    public $labelGroup;

    /**
     * list with available label groups
     * @var LabelGroupList
     */
    public $labelGroupList;

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'cssClassName' => $this->cssClassName,
            'groupID' => $this->groupID,
            'labelSearch' => $this->label,
            'labelGroup' => $this->labelGroup,
            'labelGroupList' => $this->labelGroupList,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlSelects = "label_group.groupName, label_group.groupDescription";
        $this->objectList->sqlJoins = "
            LEFT JOIN   wcf1_label_group label_group
            ON          label_group.groupID = label.groupID";
        if ($this->labelGroup) {
            $this->objectList->getConditionBuilder()->add('label.groupID = ?', [$this->labelGroup->groupID]);

            // Ramp up the limit to display all labels at once for easier
            // drag & drop sorting. This isn't exactly infinite, but if
            // you have a label group with more than 1k labels, being able
            // to sort them is the least of your problems.
            $this->itemsPerPage = 1000;
        }
        if ($this->cssClassName) {
            $this->objectList->getConditionBuilder()->add(
                'label.cssClassName LIKE ?',
                ['%' . \addcslashes($this->cssClassName, '_%') . '%']
            );
        }

        if ($this->label) {
            $languageItemList = new LanguageItemList();
            $languageItemList->getConditionBuilder()->add(
                'languageCategoryID = ?',
                [LanguageFactory::getInstance()->getCategory('wcf.acp.label')->languageCategoryID]
            );
            $languageItemList->getConditionBuilder()->add('languageID = ?', [WCF::getLanguage()->languageID]);
            $languageItemList->getConditionBuilder()->add('languageItem LIKE ?', ['wcf.acp.label.label%']);
            $languageItemList->getConditionBuilder()->add(
                'languageItemValue LIKE ?',
                ['%' . \addcslashes($this->label, '_%') . '%']
            );
            $languageItemList->readObjects();

            $labelIDs = [];
            foreach ($languageItemList as $languageItem) {
                $labelIDs[] = \str_replace('wcf.acp.label.label', '', $languageItem->languageItem);
            }

            if (!empty($labelIDs)) {
                $this->objectList->getConditionBuilder()->add(
                    '(label LIKE ? OR labelID IN (?))',
                    ['%' . \addcslashes($this->label, '_%') . '%', $labelIDs]
                );
            } else {
                $this->objectList->getConditionBuilder()->add(
                    'label LIKE ?',
                    ['%' . \addcslashes($this->label, '_%') . '%']
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $this->labelGroupList = new LabelGroupList();
        $this->labelGroupList->readObjects();
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_POST)) {
            $parameters = [];
            if (!empty($_POST['groupID'])) {
                $parameters['id'] = \intval($_POST['groupID']);
            }
            if (!empty($_POST['label'])) {
                $parameters['label'] = StringUtil::trim($_POST['label']);
            }
            if (!empty($_POST['cssClassName'])) {
                $parameters['cssClassName'] = StringUtil::trim($_POST['cssClassName']);
            }

            if (!empty($parameters)) {
                HeaderUtil::redirect(LinkHandler::getInstance()->getLink('LabelList', $parameters));

                exit;
            }
        }

        if (isset($_REQUEST['id'])) {
            $this->groupID = \intval($_REQUEST['id']);
        }
        if (isset($_REQUEST['label'])) {
            $this->label = StringUtil::trim($_REQUEST['label']);
        }
        if (isset($_REQUEST['cssClassName'])) {
            $this->cssClassName = StringUtil::trim($_REQUEST['cssClassName']);
        }

        if ($this->groupID) {
            $this->labelGroup = new LabelGroup($this->groupID);
            if (!$this->labelGroup->groupID) {
                throw new IllegalLinkException();
            }

            $this->defaultSortField = 'showOrder';
        }
    }
}
