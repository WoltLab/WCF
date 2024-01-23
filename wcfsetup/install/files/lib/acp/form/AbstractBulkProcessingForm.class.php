<?php

namespace wcf\acp\form;

use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Abstract implementation of a form for bulk processing objects of a certain type.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
abstract class AbstractBulkProcessingForm extends AbstractForm
{
    /**
     * object action object type types
     * @var ObjectType[]
     */
    public $actions = [];

    /**
     * number of objects affected by bulk processing
     * @var int
     */
    public $affectedObjectCount = 0;

    /**
     * object condition object type types
     * @var ObjectType[][]
     */
    public $conditions = [];

    /**
     * list with bulk processed objects
     * @var \wcf\data\DatabaseObjectList
     */
    public $objectList;

    /**
     * bulk processable object type
     * @var ObjectType
     */
    public $objectType;

    /**
     * name of the bulk processable object type
     * @var string
     */
    public $objectTypeName = '';

    /**
     * @inheritDoc
     */
    public $templateName = 'bulkProcessing';

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        $classParts = \explode('\\', static::class);

        WCF::getTPL()->assign([
            'actions' => $this->actions,
            'affectedObjectCount' => $this->affectedObjectCount,
            'controller' => \str_replace('Form', '', \array_pop($classParts)),
            'controllerApplication' => $classParts[0],
            'conditions' => $this->conditions,
            'objectType' => $this->objectType,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        // read bulk processable object type
        $this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.bulkProcessableObject',
            $this->objectTypeName
        );
        if ($this->objectType === null) {
            throw new \LogicException("Unknown bulk processable object type '" . $this->objectTypeName . "'");
        }

        // read conditions
        if (ObjectTypeCache::getInstance()->getDefinitionByName($this->objectType->getProcessor()->getConditionObjectTypeDefinition()) === null) {
            throw new \LogicException("Unknown condition object type definition '" . $this->objectType->getProcessor()->getConditionObjectTypeDefinition() . "'");
        }
        $conditionObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes($this->objectType->getProcessor()->getConditionObjectTypeDefinition());
        if (empty($conditionObjectTypes)) {
            throw new IllegalLinkException();
        }

        foreach ($conditionObjectTypes as $objectType) {
            if ($objectType->conditiongroup) {
                if (!isset($this->conditions[$objectType->conditiongroup])) {
                    $this->conditions[$objectType->conditiongroup] = [];
                }

                $this->conditions[$objectType->conditiongroup][$objectType->objectTypeID] = $objectType;
            } else {
                $this->conditions[''][$objectType->objectTypeID] = $objectType;
            }
        }

        // read actions
        if (ObjectTypeCache::getInstance()->getDefinitionByName($this->objectType->getProcessor()->getActionObjectTypeDefinition()) === null) {
            throw new \LogicException("Unknown action object type definition '" . $this->objectType->getProcessor()->getActionObjectTypeDefinition() . "'");
        }

        $actions = ObjectTypeCache::getInstance()->getObjectTypes($this->objectType->getProcessor()->getActionObjectTypeDefinition());
        foreach ($actions as $objectType) {
            if (isset($this->actions[$objectType->action])) {
                throw new \LogicException("Duplicate action with name '" . $objectType->action . "'");
            }

            if ($objectType->validateOptions() && $objectType->validatePermissions()) {
                $this->actions[$objectType->action] = $objectType;
            }
        }
        if (empty($this->actions)) {
            throw new IllegalLinkException();
        }

        parent::readData();

        if (empty($_POST)) {
            if (isset($_REQUEST['success']) && isset($_REQUEST['count'])) {
                $this->affectedObjectCount = \intval($_REQUEST['count']);
                WCF::getTPL()->assign('success', true);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        foreach ($this->conditions as $groupedObjectTypes) {
            foreach ($groupedObjectTypes as $objectType) {
                $objectType->getProcessor()->readFormParameters();
            }
        }

        if (isset($this->actions[$this->action])) {
            $this->actions[$this->action]->getProcessor()->readFormParameters();
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        $this->objectList = $this->actions[$this->action]->getProcessor()->getObjectList();

        parent::save();

        // read objects
        foreach ($this->conditions as $groupedObjectTypes) {
            foreach ($groupedObjectTypes as $objectType) {
                $data = $objectType->getProcessor()->getData();
                if ($data !== null) {
                    $objectType->getProcessor()->addObjectListCondition($this->objectList, $data);
                }
            }
        }

        $this->objectList->readObjectIDs();

        $this->affectedObjectCount = \count($this->objectList->getObjectIDs());
        // save config in session
        $bulkProcessingData = WCF::getSession()->getVar('bulkProcessingData');
        if ($bulkProcessingData === null) {
            $bulkProcessingData = [];
        }
        $bulkProcessingData[$this->affectedObjectCount] = [
            'action' => $this->actions[$this->action]->getProcessor(),
            'objectIDs' => $this->objectList->getObjectIDs(),
            'form' => LinkHandler::getInstance()->getControllerLink(get_called_class(), [
                'isACP' => true,
                'success' => true,
                'count' => $this->affectedObjectCount,
            ]),
        ];
        WCF::getSession()->register('bulkProcessingData', $bulkProcessingData);

        $this->saved();

        WCF::getTPL()->assign('bulkProcessingID', $this->affectedObjectCount);
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        // validate action
        if (empty($this->action)) {
            throw new UserInputException('action');
        }

        if (!isset($this->actions[$this->action])) {
            throw new UserInputException('action', 'noValidSelection');
        }

        $this->actions[$this->action]->getProcessor()->validate();

        // validate conditions
        foreach ($this->conditions as $groupedObjectTypes) {
            foreach ($groupedObjectTypes as $objectType) {
                $objectType->getProcessor()->validate();
            }
        }
    }
}
