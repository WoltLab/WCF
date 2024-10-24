<?php

namespace wcf\acp\page;

use wcf\data\label\group\LabelGroupList;
use wcf\data\language\item\LanguageItemList;
use wcf\page\SortablePage;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Lists available label groups.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    LabelGroupList $objectList
 */
class LabelGroupListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.label.group.list';

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'showOrder';

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 50;

    /**
     * @inheritDoc
     */
    public $validSortFields = ['groupID', 'groupName', 'groupDescription', 'showOrder', 'labels'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.label.canManageLabel'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = LabelGroupList::class;

    /**
     * @var string
     */
    public $groupName = '';

    /**
     * @var string
     */
    public $groupDescription = '';

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_POST)) {
            $parameters = [];
            if (!empty($_POST['groupName'])) {
                $parameters['groupName'] = StringUtil::trim($_POST['groupName']);
            }
            if (!empty($_POST['groupDescription'])) {
                $parameters['groupDescription'] = StringUtil::trim($_POST['groupDescription']);
            }

            if (!empty($parameters)) {
                HeaderUtil::redirect(LinkHandler::getInstance()->getLink('LabelGroupList', $parameters));

                exit;
            }
        }

        if (isset($_REQUEST['groupName'])) {
            $this->groupName = StringUtil::trim($_REQUEST['groupName']);
        }
        if (isset($_REQUEST['groupDescription'])) {
            $this->groupDescription = StringUtil::trim($_REQUEST['groupDescription']);
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlSelects .= '(
            SELECT  COUNT(*)
            FROM    wcf1_label
            WHERE   groupID = label_group.groupID
        ) AS labels';

        if ($this->groupName) {
            $languageItemList = new LanguageItemList();
            $languageItemList->getConditionBuilder()->add(
                'languageCategoryID = ?',
                [LanguageFactory::getInstance()->getCategory('wcf.acp.label')->languageCategoryID]
            );
            $languageItemList->getConditionBuilder()->add('languageID = ?', [WCF::getLanguage()->languageID]);
            $languageItemList->getConditionBuilder()->add('languageItem LIKE ?', ['wcf.acp.label.group%']);
            $languageItemList->getConditionBuilder()->add(
                'languageItemValue LIKE ?',
                ['%' . \addcslashes($this->groupName, '_%') . '%']
            );
            $languageItemList->readObjects();

            $labelIDs = [];
            foreach ($languageItemList as $languageItem) {
                $labelIDs[] = \str_replace('wcf.acp.label.group', '', $languageItem->languageItem);
            }

            if (!empty($labelIDs)) {
                $this->objectList->getConditionBuilder()->add(
                    '(groupName LIKE ? OR groupID IN (?))',
                    ['%' . \addcslashes($this->groupName, '_%') . '%', $labelIDs]
                );
            } else {
                $this->objectList->getConditionBuilder()->add(
                    'groupName LIKE ?',
                    ['%' . \addcslashes($this->groupName, '_%') . '%']
                );
            }
        }

        if ($this->groupDescription) {
            $this->objectList->getConditionBuilder()->add(
                'label_group.groupDescription LIKE ?',
                ['%' . \addcslashes($this->groupDescription, '_%') . '%']
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'groupName' => $this->groupName,
            'groupDescription' => $this->groupDescription,
        ]);
    }
}
