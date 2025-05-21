<?php

namespace wcf\acp\form;

use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignmentAction;
use wcf\data\user\group\UserGroup;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\condition\provider\UserConditionProvider;
use wcf\system\form\builder\container\ConditionFormContainer;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;

/**
 * Shows the form to create a new automatic user group assignment.
 *
 * @author  Olaf Braun, Matthias Schmidt
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<UserGroupAssignment>
 */
class UserGroupAssignmentAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.group.assignment.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canManageGroupAssignment'];

    /**
     * @inheritDoc
     */
    public $objectActionClass = UserGroupAssignmentAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = UserGroupAssignmentEditForm::class;

    #[\Override]
    public function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('section')
                ->appendChildren([
                    TextFormField::create('title')
                        ->label('wcf.global.name')
                        ->maximumLength(255)
                        ->required(),
                    SingleSelectionFormField::create('groupID')
                        ->label('wcf.user.group')
                        ->required()
                        ->options($this->getUserGroups()),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.acp.group.assignment.isDisabled')
                        ->value(false),
                ]),
            ConditionFormContainer::create()
                ->conditionProvider(new UserConditionProvider()),
        ]);
    }

    /**
     * @return array<int, UserGroup>
     */
    private function getUserGroups(): array
    {
        $userGroups = UserGroup::getSortedGroupsByType([], [
            UserGroup::EVERYONE,
            UserGroup::GUESTS,
            UserGroup::OWNER,
            UserGroup::USERS,
        ]);

        return \array_filter($userGroups, static fn ($userGroup) => $userGroup->isAccessible());
    }
}
