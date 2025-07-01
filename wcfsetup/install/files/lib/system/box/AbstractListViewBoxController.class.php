<?php

namespace wcf\system\box;

use wcf\system\condition\IObjectListCondition;
use wcf\system\event\EventHandler;
use wcf\system\listView\AbstractListView;
use wcf\system\WCF;

/**
 * Default implementation of a box controller based on a list view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @template TListView of AbstractListView
 */
abstract class AbstractListViewBoxController extends AbstractDatabaseObjectListBoxController
{
    /**
     * @var TListView
     */
    protected AbstractListView $listView;

    /**
     * @return TListView
     */
    protected function getListView(): AbstractListView
    {
        if (!isset($this->listView)) {
            $this->listView = $this->createListView();
            $this->listView->setAllowFiltering(false);
            $this->listView->setAllowSorting(false);
            $this->listView->setAllowBulkInteractions(false);
        }

        return $this->listView;
    }

    /**
     * @return TListView
     */
    protected abstract function createListView(): AbstractListView;

    #[\Override]
    public function hasContent()
    {
        EventHandler::getInstance()->fireAction($this, 'hasContent');

        if ($this->objectList === null) {
            $this->loadContent();
        }

        return $this->getListView()->countItems() > 0;
    }

    #[\Override]
    protected function loadContent()
    {
        EventHandler::getInstance()->fireAction($this, 'beforeLoadContent');

        if ($this->limit) {
            $this->getListView()->setFixedNumberOfItems($this->limit);
        }

        if ($this->sortOrder && $this->sortField) {
            $this->getListView()->setSortField($this->sortField);
            $this->getListView()->getSortOrder($this->sortOrder);
        }

        $this->objectList = $this->getObjectList();

        if ($this->conditionDefinition) {
            foreach ($this->box->getControllerConditions() as $condition) {
                $processor = $condition->getObjectType()->getProcessor();
                if ($processor instanceof IObjectListCondition) {
                    $processor->addObjectListCondition($this->objectList, $condition->conditionData);
                }
            }
        }

        $this->content = $this->getTemplate();

        EventHandler::getInstance()->fireAction($this, 'afterLoadContent');
    }

    #[\Override]
    protected function getObjectList()
    {
        return $this->getListView()->getObjectList();
    }

    #[\Override]
    protected function getTemplate()
    {
        return WCF::getTPL()->render('wcf', 'boxListView', [
            'listView' => $this->listView,
        ]);
    }

    #[\Override]
    protected function readObjects()
    {
        // Does nothing.
    }
}
