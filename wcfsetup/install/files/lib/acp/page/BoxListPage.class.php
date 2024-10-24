<?php

namespace wcf\acp\page;

use wcf\data\box\Box;
use wcf\data\box\BoxList;
use wcf\page\SortablePage;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of boxes.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @property    BoxList $objectList
 */
class BoxListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cms.box.list';

    /**
     * @inheritDoc
     */
    public $objectListClassName = BoxList::class;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.cms.canManageBox'];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'name';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['boxID', 'name', 'boxType', 'position', 'showOrder'];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 50;

    /**
     * name
     * @var string
     */
    public $name = '';

    /**
     * title
     * @var string
     */
    public $title = '';

    /**
     * content
     * @var string
     */
    public $content = '';

    /**
     * box type
     * @var string
     */
    public $boxType = '';

    /**
     * box position
     * @var string
     */
    public $position = '';

    /**
     * display 'Add Box' dialog on load
     * @var int
     */
    public $showBoxAddDialog = 0;

    /**
     * filters the list of boxes showing only custom boxes
     * @var bool
     */
    public $originIsNotSystem = 0;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['name'])) {
            $this->name = StringUtil::trim($_REQUEST['name']);
        }
        if (!empty($_REQUEST['title'])) {
            $this->title = StringUtil::trim($_REQUEST['title']);
        }
        if (!empty($_REQUEST['content'])) {
            $this->content = StringUtil::trim($_REQUEST['content']);
        }
        if (!empty($_REQUEST['boxType'])) {
            $this->boxType = $_REQUEST['boxType'];
        }
        if (!empty($_REQUEST['position'])) {
            $this->position = $_REQUEST['position'];
        }
        if (!empty($_REQUEST['showBoxAddDialog'])) {
            $this->showBoxAddDialog = 1;
        }
        if (!empty($_REQUEST['originIsNotSystem'])) {
            $this->originIsNotSystem = 1;
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        // hide menu boxes
        $this->objectList->getConditionBuilder()->add('box.boxType <> ?', ['menu']);

        if (!empty($this->name)) {
            $this->objectList->getConditionBuilder()->add('box.name LIKE ?', ['%' . $this->name . '%']);
        }
        if (!empty($this->title)) {
            $this->objectList->getConditionBuilder()->add(
                'box.boxID IN (
                    SELECT  boxID
                    FROM    wcf1_box_content
                    WHERE   title LIKE ?
                )',
                ['%' . $this->title . '%']
            );
        }
        if (!empty($this->content)) {
            $this->objectList->getConditionBuilder()->add(
                'box.boxID IN (
                    SELECT  boxID
                    FROM    wcf1_box_content
                    WHERE   content LIKE ?
                )',
                ['%' . $this->content . '%']
            );
        }
        if (!empty($this->position)) {
            $this->objectList->getConditionBuilder()->add('box.position = ?', [$this->position]);
        }
        if (!empty($this->boxType)) {
            $this->objectList->getConditionBuilder()->add('box.boxType = ?', [$this->boxType]);
        }
        if ($this->originIsNotSystem) {
            $this->objectList->getConditionBuilder()->add('box.originIsSystem = ?', [0]);
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'name' => $this->name,
            'title' => $this->title,
            'content' => $this->content,
            'boxType' => $this->boxType,
            'position' => $this->position,
            'availablePositions' => Box::$availablePositions,
            'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
            'showBoxAddDialog' => $this->showBoxAddDialog,
            'originIsNotSystem' => $this->originIsNotSystem,
        ]);
    }
}
