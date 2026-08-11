<?php

namespace wcf\page;

use wcf\data\DatabaseObjectList;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Provides default implementations for a sortable page of listed items.
 * Handles the sorting parameters automatically.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @template TDatabaseObjectList of DatabaseObjectList
 * @extends MultipleLinkPage<TDatabaseObjectList>
 */
abstract class SortablePage extends MultipleLinkPage
{
    /**
     * default sort field
     * @var string
     */
    public $defaultSortField = '';

    /**
     * default sort order
     * @var string
     */
    public $defaultSortOrder = 'ASC';

    /**
     * list of valid sort fields
     * @var string[]
     */
    public $validSortFields = [];

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        // read sorting parameter
        if (isset($_REQUEST['sortField'])) {
            $this->sortField = $_REQUEST['sortField'];
        }
        if (isset($_REQUEST['sortOrder'])) {
            $this->sortOrder = $_REQUEST['sortOrder'];
        }
    }

    #[\Override]
    public function readData()
    {
        $this->validateSortOrder();
        $this->validateSortField();

        parent::readData();
    }

    /**
     * Validates the given sort field parameter.
     *
     * @return void
     */
    public function validateSortField()
    {
        // call validateSortField event
        EventHandler::getInstance()->fireAction($this, 'validateSortField');

        if (!\in_array($this->sortField, $this->validSortFields, true)) {
            $this->sortField = $this->defaultSortField;
        }
    }

    /**
     * Validates the given sort order parameter.
     *
     * @return void
     */
    public function validateSortOrder()
    {
        // call validateSortOrder event
        EventHandler::getInstance()->fireAction($this, 'validateSortOrder');

        switch ($this->sortOrder) {
            case 'ASC':
            case 'DESC':
                break;

            default:
                $this->sortOrder = $this->defaultSortOrder;
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        // assign sorting parameters
        WCF::getTPL()->assign([
            'sortField' => $this->sortField,
            'sortOrder' => $this->sortOrder,
        ]);
    }
}
