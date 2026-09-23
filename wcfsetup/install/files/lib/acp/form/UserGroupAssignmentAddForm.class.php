<?php

namespace wcf\acp\form;

use wcf\command\user\group\assignment\CreateUserGroupAssignment;
use wcf\command\user\group\assignment\UpdateUserGroupAssignment;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignmentBuilder;
use wcf\data\user\group\UserGroup;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\ObjectFilterFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\object\filter\builder\UserGroupAssignmentObjectFilterBuilder;

/**
 * Shows the form to create a new automatic user group assignment.
 *
 * @author      Alexander Ebert, Matthias Schmidt
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<UserGroupAssignment, UserGroupAssignmentBuilder>
 */
class UserGroupAssignmentAddForm extends AbstractDatabaseObjectBuilderForm
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
    public string $objectEditLinkController = UserGroupAssignmentEditForm::class;

    #[\Override]
    protected function getDatabaseObjectBuilder(): UserGroupAssignmentBuilder
    {
        if ($this->formObject !== null) {
            return UserGroupAssignmentBuilder::forUpdate($this->formObject);
        }

        return UserGroupAssignmentBuilder::forCreate();
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateUserGroupAssignment($builder);
        }

        return new CreateUserGroupAssignment($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        $this->form->appendChildren([
            TitleFormField::create('title')
                ->label('wcf.global.name')
                ->maximumLength(255)
                ->required()
                ->saveValueCallback(static function (UserGroupAssignmentBuilder $builder, IFormField $field) {
                    $builder->setTitle($field->getSaveValue());
                }),
            SelectFormField::create('groupID')
                ->label('wcf.user.group')
                ->options($this->getAvailableUserGroups())
                ->required()
                ->saveValueCallback(static function (UserGroupAssignmentBuilder $builder, IFormField $field) {
                    $builder->setGroupID((int)$field->getSaveValue());
                }),
            BooleanFormField::create('isDisabled')
                ->label('wcf.acp.group.assignment.isDisabled')
                ->saveValueCallback(static function (UserGroupAssignmentBuilder $builder, IFormField $field) {
                    $builder->setIsDisabled((bool)$field->getSaveValue());
                }),
            ObjectFilterFormField::create('conditions')
                ->builder(new UserGroupAssignmentObjectFilterBuilder())
                ->required()
                ->saveValueCallback(static function (UserGroupAssignmentBuilder $builder, IFormField $field) {
                    $builder->setConditions($field->getSaveValue());
                }),
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
}
