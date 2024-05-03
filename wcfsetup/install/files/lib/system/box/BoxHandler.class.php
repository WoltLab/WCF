<?php

namespace wcf\system\box;

use wcf\data\box\Box;
use wcf\data\box\BoxList;
use wcf\data\condition\ConditionAction;
use wcf\data\page\Page;
use wcf\system\event\EventHandler;
use wcf\system\request\RequestHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles boxes.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class BoxHandler extends SingletonFactory
{
    /**
     * boxes with box id as key
     * @var Box[]
     */
    protected $boxes = [];

    /**
     * identifier to boxes
     * @var Box[]
     */
    protected $boxesByIdentifier = [];

    /**
     * boxes grouped by position
     * @var Box[][]
     */
    protected $boxesByPosition = [];

    /**
     * @var bool
     */
    protected static $disablePageLayout = false;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        // get active page id
        $pageID = 0;
        if (!self::$disablePageLayout) {
            if (($request = RequestHandler::getInstance()->getActiveRequest()) !== null) {
                $pageID = $request->getPageID();
            }
        }

        $this->boxesByPosition = self::loadBoxes($pageID, !RequestHandler::getInstance()->isACPRequest());
        foreach ($this->boxesByPosition as $boxes) {
            foreach ($boxes as $box) {
                $this->boxes[$box->boxID] = $box;
                $this->boxesByIdentifier[$box->identifier] = $box;
            }
        }
    }

    /**
     * Creates a new condition for an existing box.
     *
     * Note: The primary use of this method is to be used during package installation.
     *
     * @param string $boxIdentifier
     * @param string $conditionDefinition
     * @param string $conditionObjectType
     * @param array $conditionData
     * @throws  \InvalidArgumentException
     */
    public function createBoxCondition($boxIdentifier, $conditionDefinition, $conditionObjectType, array $conditionData)
    {
        // do not rely on caches during package installation
        $sql = "SELECT      objectTypeID
                FROM        wcf" . WCF_N . "_object_type object_type
                INNER JOIN  wcf" . WCF_N . "_object_type_definition object_type_definition
                ON          object_type.definitionID = object_type_definition.definitionID
                WHERE       objectType = ?
                        AND definitionName = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$conditionObjectType, $conditionDefinition]);
        $objectTypeID = $statement->fetchSingleColumn();

        if (!$objectTypeID) {
            throw new \InvalidArgumentException("Unknown box condition '{$conditionObjectType}' of condition definition '{$conditionDefinition}'");
        }

        $box = Box::getBoxByIdentifier($boxIdentifier);
        if ($box === null) {
            throw new \InvalidArgumentException("Unknown box with identifier '{$boxIdentifier}'");
        }

        (new ConditionAction([], 'create', [
            'data' => [
                'conditionData' => \serialize($conditionData),
                'objectID' => $box->boxID,
                'objectTypeID' => $objectTypeID,
            ],
        ]))->executeAction();
    }

    /**
     * Returns the box with the given id or `null` if it does not exist.
     *
     * @param int $boxID
     * @return  Box|null
     */
    public function getBox($boxID)
    {
        return $this->boxes[$boxID] ?? null;
    }

    /**
     * Returns boxes for the given position.
     *
     * @param string $position
     * @return  Box[]
     */
    public function getBoxes($position)
    {
        return $this->boxesByPosition[$position] ?? [];
    }

    /**
     * Returns the box with given identifier or `null` if there is no such box.
     *
     * @param string $identifier
     * @return  Box|null
     */
    public function getBoxByIdentifier($identifier)
    {
        return $this->boxesByIdentifier[$identifier] ?? null;
    }

    /**
     * Assigns pages to a certain box.
     *
     * Note: The primary use of this method is to be used during package installation.
     *
     * @param string $boxIdentifier
     * @param string[] $pageIdentifiers
     * @param bool $visible
     * @throws  \InvalidArgumentException
     */
    public function addBoxToPageAssignments($boxIdentifier, array $pageIdentifiers, $visible = true)
    {
        $box = Box::getBoxByIdentifier($boxIdentifier);
        if ($box === null) {
            throw new \InvalidArgumentException("Unknown box with identifier '{$boxIdentifier}'");
        }

        $pages = [];
        foreach ($pageIdentifiers as $pageIdentifier) {
            $page = Page::getPageByIdentifier($pageIdentifier);
            if ($page === null) {
                throw new \InvalidArgumentException("Unknown page with identifier '{$pageIdentifier}'");
            }
            $pages[] = $page;
        }

        if (($visible && $box->visibleEverywhere) || (!$visible && !$box->visibleEverywhere)) {
            $sql = "DELETE FROM     wcf" . WCF_N . "_box_to_page
                    WHERE           boxID = ?
                            AND pageID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            foreach ($pages as $page) {
                $statement->execute([$box->boxID, $page->pageID]);
            }
        } else {
            $sql = "REPLACE INTO    wcf" . WCF_N . "_box_to_page
                                    (boxID, pageID, visible)
                    VALUES          (?, ?, ?)";
            $statement = WCF::getDB()->prepareStatement($sql);
            foreach ($pages as $page) {
                $statement->execute([$box->boxID, $page->pageID, $visible ? 1 : 0]);
            }
        }
    }

    /**
     * Returns true if the left sidebar contains at least one visible menu.
     *
     * @return      bool
     * @since       3.1
     */
    public function sidebarLeftHasMenu()
    {
        foreach ($this->getBoxes('sidebarLeft') as $box) {
            if ($box->getMenu() && $box->getMenu()->hasContent()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Disables the loading of the box layout for the active page.
     */
    public static function disablePageLayout()
    {
        self::$disablePageLayout = true;
    }

    /**
     * Returns the list of boxes sorted by their global and page-local show order.
     *
     * @param int $pageID page id
     * @param bool $forDisplay enables content loading and removes inaccessible boxes from view
     * @return      Box[][]
     */
    public static function loadBoxes($pageID, $forDisplay)
    {
        // load box layout for active page
        $boxList = new BoxList();
        if ($forDisplay) {
            $boxList->getConditionBuilder()->add("box.isDisabled = ?", [0]);
        }
        if ($pageID) {
            $boxList->getConditionBuilder()->add(
                '
                (
                    (box.visibleEverywhere = ?
                    AND boxID NOT IN (
                        SELECT  boxID
                        FROM    wcf' . WCF_N . '_box_to_page
                        WHERE   pageID = ?
                            AND visible = ?
                    )) OR
                    boxID IN (
                        SELECT  boxID
                        FROM    wcf' . WCF_N . '_box_to_page
                        WHERE   pageID = ?
                            AND visible = ?
                    )
                )',
                [1, $pageID, 0, $pageID, 1]
            );
        } else {
            $boxList->getConditionBuilder()->add('box.visibleEverywhere = ?', [1]);
        }

        if ($forDisplay) {
            $boxList->enableContentLoading();
        }

        $boxList->readObjects();

        $showOrders = [];
        if ($pageID) {
            $sql = "SELECT  boxID, showOrder
                    FROM    wcf" . WCF_N . "_page_box_order
                    WHERE   pageID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$pageID]);
            while ($row = $statement->fetchArray()) {
                $showOrders[$row['boxID']] = $row['showOrder'];
            }
        }

        $boxes = [];
        foreach ($boxList as $box) {
            if (!$forDisplay || ($box->isAccessible() && $box->isVisible())) {
                $virtualShowOrder = (isset($showOrders[$box->boxID])) ? $showOrders[$box->boxID] : ($box->showOrder + 1000);
                $box->setVirtualShowOrder($virtualShowOrder);

                if (!isset($boxes[$box->position])) {
                    $boxes[$box->position] = [];
                }
                $boxes[$box->position][] = $box;
            }
        }

        $parameters = [
            'boxes' => $boxes,
            'forDisplay' => $forDisplay,
            'pageID' => $pageID,
        ];

        EventHandler::getInstance()->fireAction(static::class, 'loadBoxes', $parameters);

        if (!isset($parameters['boxes']) || !\is_array($parameters['boxes'])) {
            throw new \UnexpectedValueException("'boxes' parameter is no longer an array.");
        }

        $boxes = $parameters['boxes'];

        foreach ($boxes as &$positionBoxes) {
            \usort($positionBoxes, static function ($a, $b) {
                if ($a->virtualShowOrder == $b->virtualShowOrder) {
                    return 0;
                }

                return ($a->virtualShowOrder < $b->virtualShowOrder) ? -1 : 1;
            });
        }
        unset($positionBoxes);

        return $boxes;
    }

    /**
     * Returns true if provided page id uses a custom box show order.
     *
     * @param int $pageID page id
     * @return      bool         true if there is a custom show order for boxes
     */
    public static function hasCustomShowOrder($pageID)
    {
        $sql = "SELECT  COUNT(*) AS count
                FROM    wcf" . WCF_N . "_page_box_order
                WHERE   pageID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$pageID]);

        return $statement->fetchSingleColumn() > 0;
    }
}
