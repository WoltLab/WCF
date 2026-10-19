<?php

namespace wcf\acp\form;

use wcf\command\user\group\assignment\CreateUserGroupAssignment;
use wcf\command\user\group\assignment\UpdateUserGroupAssignment;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignmentBuilder;
use wcf\data\user\group\assignment\UserGroupAssignmentList;
use wcf\data\user\group\UserGroup;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\ObjectFilterFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\object\filter\builder\UserGroupAssignmentObjectFilterBuilder;
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;

/**
 * Shows the form to create a new automatic user group assignment.
 *
 * @author      Alexander Ebert, Matthias Schmidt, Marcel Werk
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
            FormContainer::create('data')
                ->appendChildren([
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
                        ->addValidator($this->getGroupIDValidator())
                        ->saveValueCallback(static function (UserGroupAssignmentBuilder $builder, IFormField $field) {
                            $builder->setGroupID((int)$field->getSaveValue());
                        }),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.acp.group.assignment.isDisabled')
                        ->saveValueCallback(static function (UserGroupAssignmentBuilder $builder, IFormField $field) {
                            $builder->setIsDisabled((bool)$field->getSaveValue());
                        }),
                    BooleanFormField::create('revokeAutomatically')
                        ->label('wcf.acp.group.assignment.revokeAutomatically')
                        ->description('wcf.acp.group.assignment.revokeAutomatically.description')
                        ->addValidator($this->getRevokeAutomaticallyValidator())
                        ->saveValueCallback(static function (UserGroupAssignmentBuilder $builder, IFormField $field) {
                            $builder->setRevokeAutomatically((bool)$field->getSaveValue());
                        }),
                ]),
            FormContainer::create('conditionsContainer')
                ->label('wcf.acp.group.assignment.conditions')
                ->description('wcf.acp.group.assignment.conditions.description')
                ->appendChild(
                    ObjectFilterFormField::create('conditions')
                        ->builder(new UserGroupAssignmentObjectFilterBuilder())
                        ->required()
                        ->saveValueCallback(static function (UserGroupAssignmentBuilder $builder, IFormField $field) {
                            $builder->setConditions($field->getSaveValue());
                        }),
                ),
        ]);
    }

    /**
     * Rejects additional assignments for user groups whose assignment
     * automatically removes users, which only supports a single assignment.
     */
    private function getGroupIDValidator(): FormFieldValidator
    {
        return new FormFieldValidator('revokeAutomatically', function (SelectFormField $field) {
            $revokeField = $field->getDocument()->getNodeById('revokeAutomatically');
            \assert($revokeField instanceof BooleanFormField);
            if ($revokeField->getValue() === true) {
                // Conflicts are reported by the validator of the automatic revocation.
                return;
            }

            foreach ($this->getOtherAssignments((int)$field->getSaveValue()) as $assignment) {
                if ($assignment->revokeAutomatically === 1) {
                    $field->addValidationError(new FormFieldValidationError(
                        'revokeAutomatically',
                        'wcf.acp.group.assignment.groupID.error.revokeAutomatically'
                    ));

                    return;
                }
            }
        });
    }

    /**
     * Rejects the automatic revocation for user groups that grant administrative
     * access or have other assignments and for conditions that exclude members
     * of the target user group, which would repeatedly add and remove the same
     * users.
     */
    private function getRevokeAutomaticallyValidator(): FormFieldValidator
    {
        return new FormFieldValidator('revokeAutomatically', function (BooleanFormField $field) {
            if ($field->getValue() !== true) {
                return;
            }

            $groupField = $field->getDocument()->getNodeById('groupID');
            \assert($groupField instanceof SelectFormField);
            $groupID = (int)$groupField->getSaveValue();

            $group = UserGroup::getGroupByID($groupID);
            if ($group !== null && !UserGroupAssignmentHandler::isRevocableGroup($group)) {
                $field->addValidationError(new FormFieldValidationError(
                    'adminGroup',
                    'wcf.acp.group.assignment.revokeAutomatically.error.adminGroup'
                ));

                return;
            }

            if ($this->getOtherAssignments($groupID) !== []) {
                $field->addValidationError(new FormFieldValidationError(
                    'otherAssignments',
                    'wcf.acp.group.assignment.revokeAutomatically.error.otherAssignments'
                ));

                return;
            }

            $conditionsField = $field->getDocument()->getNodeById('conditions');
            \assert($conditionsField instanceof ObjectFilterFormField);
            if (UserGroupAssignmentHandler::excludesTargetGroup($groupID, $conditionsField->getValue())) {
                $field->addValidationError(new FormFieldValidationError(
                    'notInTargetGroup',
                    'wcf.acp.group.assignment.revokeAutomatically.error.notInTargetGroup'
                ));
            }
        });
    }

    /**
     * Returns the other assignments for the given user group, including the
     * disabled ones because they can be enabled without using this form.
     *
     * @return array<int, UserGroupAssignment>
     */
    private function getOtherAssignments(int $groupID): array
    {
        $assignmentList = new UserGroupAssignmentList();
        $assignmentList->getConditionBuilder()->add('groupID = ?', [$groupID]);
        if ($this->formObject !== null) {
            $assignmentList->getConditionBuilder()->add('assignmentID <> ?', [$this->formObject->assignmentID]);
        }
        $assignmentList->readObjects();

        return $assignmentList->getObjects();
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
