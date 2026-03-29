<?php

namespace wcf\acp\form;

use wcf\data\object\type\ObjectType;
use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignmentAction;
use wcf\data\user\group\UserGroup;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\UserInputException;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ObjectFilterFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\request\LinkHandler;
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the form to create a new automatic user group assignment.
 *
 * @author      Alexander Ebert, Matthias Schmidt
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @implements  AbstractFormBuilderForm<UserGroupAssignment>
 */
class UserGroupAssignmentAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.group.assignment.add';

    /**
     * list of grouped user group assignment condition object types
     * @var ObjectType[][]
     */
    public $conditions = [];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canManageGroupAssignment'];

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            TitleFormField::create('title')
                ->label('wcf.global.name')
                ->maximumLength(255)
                ->required(),
            SelectFormField::create('groupID')
                ->label('wcf.user.group')
                ->options($this->getAvailableUserGroups())
                ->required(),
            BooleanFormField::create('isDisabled')
                ->label('wcf.acp.group.assignment.isDisabled'),
            ObjectFilterFormField::create('conditions')
        ]);
    }

    /**
     * @return array<int, UserGroup>
     */
    private function getAvailableUserGroups(): array
    {
        return \array_filter(
            UserGroup::getSortedGroupsByType([], [
                UserGroup::EVERYONE,
                UserGroup::GUESTS,
                UserGroup::OWNER,
                UserGroup::USERS,
            ]),
            static fn(UserGroup $userGroup) => $userGroup->isAccessible(),
        );
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'action' => 'add',
            'groupedObjectTypes' => $this->conditions,
        ]);
    }

    #[\Override]
    public function readData()
    {
        $this->conditions = UserGroupAssignmentHandler::getInstance()->getGroupedObjectTypes();

        parent::readData();
    }

    #[\Override]
    public function readFormParameters()
    {
        parent::readFormParameters();

        foreach ($this->conditions as $conditions) {
            /** @var ObjectType $condition */
            foreach ($conditions as $condition) {
                $condition->getProcessor()->readFormParameters();
            }
        }
    }

    #[\Override]
    public function save()
    {
        parent::save();

        $this->objectAction = new UserGroupAssignmentAction([], 'create', [
            'data' => \array_merge($this->additionalFields, [
                /*
                'groupID' => $this->groupID,
                'isDisabled' => $this->isDisabled,
                'title' => $this->title,
                */]),
        ]);
        $returnValues = $this->objectAction->executeAction();

        // transform conditions array into one-dimensional array
        $conditions = [];
        foreach ($this->conditions as $groupedObjectTypes) {
            $conditions = \array_merge($conditions, $groupedObjectTypes);
        }

        ConditionHandler::getInstance()->createConditions($returnValues['returnValues']->assignmentID, $conditions);

        $this->saved();

        foreach ($this->conditions as $conditions) {
            foreach ($conditions as $condition) {
                $condition->getProcessor()->reset();
            }
        }

        WCF::getTPL()->assign([
            'success' => true,
            'objectEditLink' => LinkHandler::getInstance()->getControllerLink(
                UserGroupAssignmentEditForm::class,
                ['id' => $returnValues['returnValues']->assignmentID]
            ),
        ]);
    }

    #[\Override]
    public function validate()
    {
        parent::validate();

        $hasData = false;
        foreach ($this->conditions as $conditions) {
            foreach ($conditions as $condition) {
                $condition->getProcessor()->validate();

                if (!$hasData && $condition->getProcessor()->getData() !== null) {
                    $hasData = true;
                }
            }
        }

        if (!$hasData) {
            throw new UserInputException('conditions');
        }
    }
}
