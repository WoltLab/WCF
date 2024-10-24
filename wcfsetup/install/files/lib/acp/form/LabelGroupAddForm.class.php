<?php

namespace wcf\acp\form;

use wcf\data\label\group\LabelGroupAction;
use wcf\data\label\group\LabelGroupEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractForm;
use wcf\system\acl\ACLHandler;
use wcf\system\exception\UserInputException;
use wcf\system\label\object\type\ILabelObjectTypeHandler;
use wcf\system\label\object\type\LabelObjectTypeContainer;
use wcf\system\language\I18nHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the label group add form.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LabelGroupAddForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.label.group.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.label.canManageLabel'];

    /**
     * force users to select a label
     * @var bool
     */
    public $forceSelection = false;

    /**
     * group name
     * @var string
     */
    public $groupName = '';

    /**
     * group description
     * @var string
     */
    public $groupDescription = '';

    /**
     * list of label object type handlers
     * @var ILabelObjectTypeHandler[]
     */
    public $labelObjectTypes = [];

    /**
     * list of label object type containers
     * @var LabelObjectTypeContainer[]
     */
    public $labelObjectTypeContainers = [];

    /**
     * list of label group to object type relations
     * @var array<array>
     */
    public $objectTypes = [];

    /**
     * object type id
     * @var int
     */
    public $objectTypeID = 0;

    /**
     * show order
     * @var int
     */
    public $showOrder = 0;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        $this->objectTypeID = ACLHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.label');

        I18nHandler::getInstance()->register('groupName');
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        I18nHandler::getInstance()->readValues();

        if (I18nHandler::getInstance()->isPlainValue('groupName')) {
            $this->groupName = I18nHandler::getInstance()->getValue('groupName');
        }

        if (isset($_POST['groupDescription'])) {
            $this->groupDescription = StringUtil::trim($_POST['groupDescription']);
        }
        if (isset($_POST['forceSelection'])) {
            $this->forceSelection = true;
        }
        if (isset($_POST['objectTypes']) && \is_array($_POST['objectTypes'])) {
            $this->objectTypes = $_POST['objectTypes'];
        }
        if (isset($_POST['showOrder'])) {
            $this->showOrder = \intval($_POST['showOrder']);
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        // get label object type handlers
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.label.objectType');
        foreach ($objectTypes as $objectType) {
            $this->labelObjectTypes[$objectType->objectTypeID] = $objectType->getProcessor();
            $this->labelObjectTypes[$objectType->objectTypeID]->setObjectTypeID($objectType->objectTypeID);
        }

        foreach ($this->labelObjectTypes as $objectTypeID => $labelObjectType) {
            $this->labelObjectTypeContainers[$objectTypeID] = $labelObjectType->getContainer();
        }

        parent::readData();

        // assign new values for object relations
        $this->setObjectTypeRelations();
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        // validate group name
        if (!I18nHandler::getInstance()->validateValue('groupName')) {
            if (I18nHandler::getInstance()->isPlainValue('groupName')) {
                throw new UserInputException('groupName');
            } else {
                throw new UserInputException('groupName', 'multilingual');
            }
        }

        // validate object type relations
        foreach ($this->objectTypes as $objectTypeID => $data) {
            if (!isset($this->labelObjectTypes[$objectTypeID])) {
                unset($this->objectTypes[$objectTypeID]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        // save label
        $this->objectAction = new LabelGroupAction([], 'create', [
            'data' => \array_merge($this->additionalFields, [
                'forceSelection' => $this->forceSelection ? 1 : 0,
                'groupName' => $this->groupName,
                'groupDescription' => $this->groupDescription,
                'showOrder' => $this->showOrder,
            ]),
        ]);
        $returnValues = $this->objectAction->executeAction();

        if (!I18nHandler::getInstance()->isPlainValue('groupName')) {
            I18nHandler::getInstance()->save(
                'groupName',
                'wcf.acp.label.group' . $returnValues['returnValues']->groupID,
                'wcf.acp.label',
                1
            );

            // update group name
            $groupEditor = new LabelGroupEditor($returnValues['returnValues']);
            $groupEditor->update([
                'groupName' => 'wcf.acp.label.group' . $returnValues['returnValues']->groupID,
            ]);
        }

        // save acl
        ACLHandler::getInstance()->save($returnValues['returnValues']->groupID, $this->objectTypeID);
        ACLHandler::getInstance()->disableAssignVariables();

        // save object type relations
        $this->saveObjectTypeRelations($returnValues['returnValues']->groupID);

        foreach ($this->labelObjectTypes as $labelObjectType) {
            $labelObjectType->save();
        }

        $this->saved();

        // reset values
        $this->forceSelection = false;
        $this->groupName = $this->groupDescription = '';
        $this->objectTypes = [];
        $this->showOrder = 0;
        $this->setObjectTypeRelations();

        // show success message
        WCF::getTPL()->assign([
            'success' => true,
            'objectEditLink' => LinkHandler::getInstance()->getControllerLink(
                LabelGroupEditForm::class,
                ['id' => $returnValues['returnValues']->groupID]
            ),
        ]);

        I18nHandler::getInstance()->reset();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        ACLHandler::getInstance()->assignVariables($this->objectTypeID);
        I18nHandler::getInstance()->assignVariables();

        WCF::getTPL()->assign([
            'action' => 'add',
            'forceSelection' => $this->forceSelection,
            'groupName' => $this->groupName,
            'groupDescription' => $this->groupDescription,
            'labelObjectTypeContainers' => $this->labelObjectTypeContainers,
            'objectTypeID' => $this->objectTypeID,
            'showOrder' => $this->showOrder,
        ]);
    }

    /**
     * Saves label group to object relations.
     *
     * @param int $groupID
     */
    protected function saveObjectTypeRelations($groupID)
    {
        WCF::getDB()->beginTransaction();

        // remove old relations
        if ($groupID !== null) {
            $sql = "DELETE FROM wcf1_label_group_to_object
                    WHERE       groupID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$groupID]);
        }

        // insert new relations
        if (!empty($this->objectTypes)) {
            $sql = "INSERT INTO wcf1_label_group_to_object
                                (groupID, objectTypeID, objectID)
                    VALUES      (?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);

            foreach ($this->objectTypes as $objectTypeID => $data) {
                foreach ($data as $objectID) {
                    // use "0" (stored as NULL) for simple true/false states
                    if (!$objectID) {
                        $objectID = null;
                    }

                    $statement->execute([
                        $groupID,
                        $objectTypeID,
                        $objectID,
                    ]);
                }
            }
        }

        WCF::getDB()->commitTransaction();
    }

    /**
     * Sets object type relations.
     *
     * @param array|null $data
     */
    protected function setObjectTypeRelations($data = null)
    {
        if (!empty($_POST)) {
            // use POST data
            $data = &$this->objectTypes;
        }

        foreach ($this->labelObjectTypeContainers as $objectTypeID => $container) {
            $hasData = isset($data[$objectTypeID]);
            foreach ($container as $object) {
                if (!$hasData) {
                    $object->setOptionValue(0);
                } else {
                    $optionValue = \in_array($object->getObjectID(), $data[$objectTypeID]) ? 1 : 0;
                    $object->setOptionValue($optionValue);
                }
            }
        }
    }
}
