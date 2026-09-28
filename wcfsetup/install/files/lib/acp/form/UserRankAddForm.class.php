<?php

namespace wcf\acp\form;

use wcf\command\user\rank\CreateUserRank;
use wcf\command\user\rank\UpdateUserRank;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\user\group\UserGroup;
use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankBuilder;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BadgeColorFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\FileProcessorFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\WCF;

/**
 * Shows the user rank add form.
 *
 * @author      Olaf Braun, Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<UserRank, UserRankBuilder>
 */
class UserRankAddForm extends AbstractDatabaseObjectBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.rank.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.rank.canManageRank'];

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_USER_RANK'];

    /**
     * @inheritDoc
     */
    public string $objectEditLinkController = UserRankEditForm::class;

    #[\Override]
    protected function getDatabaseObjectBuilder(): UserRankBuilder
    {
        if ($this->formObject !== null) {
            return UserRankBuilder::forUpdate($this->formObject);
        }

        return UserRankBuilder::forCreate();
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateUserRank($builder);
        }

        return new CreateUserRank($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('section')
                ->appendChildren([
                    TextFormField::create('rankTitle')
                        ->label('wcf.acp.user.rank.title')
                        ->l10n()
                        ->required()
                        ->maximumLength(255)
                        ->saveValueCallback(static function (UserRankBuilder $builder, TextFormField $field): void {
                            $builder->setRankTitle($field->getL10nValues());
                        })
                        ->loadValueCallback(static function (UserRank $object, IFormField $field): void {
                            $field->value($object->getL10nValues('rankTitle'));
                        }),
                    BadgeColorFormField::create('cssClassName')
                        ->label('wcf.acp.user.rank.cssClassName')
                        ->description('wcf.acp.user.rank.cssClassName.description')
                        ->textReferenceNodeId('rankTitle')
                        ->defaultLabelText(WCF::getLanguage()->get('wcf.acp.user.rank.title'))
                        ->required()
                        ->saveValueCallback(static function (UserRankBuilder $builder, BadgeColorFormField $field): void {
                            $builder->setCssClassName($field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserRank $object, IFormField $field): void {
                            $field->value($object->cssClassName);
                        }),
                ]),
            FormContainer::create('imageContainer')
                ->label('wcf.acp.user.rank.image')
                ->appendChildren([
                    FileProcessorFormField::create('rankImageFileID')
                        ->objectType('com.woltlab.wcf.user.rank.image')
                        ->label('wcf.acp.user.rank.image')
                        ->singleFileUpload()
                        ->bigPreview()
                        ->saveValueCallback(static function (UserRankBuilder $builder, FileProcessorFormField $field): void {
                            $builder->setRankImageFileID($field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserRank $object, IFormField $field): void {
                            if ($object->rankImageFileID !== null) {
                                $field->value($object->rankImageFileID);
                            }
                        }),
                    IntegerFormField::create('repeatImage')
                        ->label('wcf.acp.user.rank.repeatImage')
                        ->description('wcf.acp.user.rank.repeatImage.description')
                        ->addFieldClass('tiny')
                        ->minimum(1)
                        ->value(1)
                        ->saveValueCallback(static function (UserRankBuilder $builder, IntegerFormField $field): void {
                            $builder->setRepeatImage($field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserRank $object, IFormField $field): void {
                            $field->value($object->repeatImage);
                        }),
                    BooleanFormField::create('hideTitle')
                        ->label('wcf.acp.user.rank.hideTitle')
                        ->description('wcf.acp.user.rank.hideTitle.description')
                        ->value(false)
                        ->saveValueCallback(static function (UserRankBuilder $builder, BooleanFormField $field): void {
                            $builder->setHideTitle((bool)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserRank $object, IFormField $field): void {
                            $field->value($object->hideTitle);
                        }),
                ]),
            FormContainer::create('requirementsContainer')
                ->label('wcf.acp.user.rank.requirement')
                ->appendChildren([
                    SingleSelectionFormField::create('groupID')
                        ->label('wcf.user.group')
                        ->description('wcf.acp.user.rank.userGroup.description')
                        ->options(UserGroup::getSortedGroupsByType([], [UserGroup::GUESTS, UserGroup::EVERYONE]))
                        ->required()
                        ->saveValueCallback(static function (UserRankBuilder $builder, SingleSelectionFormField $field): void {
                            $builder->setGroupID((int)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserRank $object, IFormField $field): void {
                            $field->value($object->groupID);
                        }),
                    SelectFormField::create('requiredGender')
                        ->label('wcf.user.option.gender')
                        ->description('wcf.acp.user.rank.requiredGender.description')
                        ->options([
                            1 => 'wcf.user.gender.male',
                            2 => 'wcf.user.gender.female',
                            3 => 'wcf.user.gender.other'
                        ])
                        ->saveValueCallback(static function (UserRankBuilder $builder, SelectFormField $field): void {
                            $builder->setRequiredGender((int)($field->getSaveValue() ?? 0));
                        })
                        ->loadValueCallback(static function (UserRank $object, IFormField $field): void {
                            // `0` means that no specific gender is required, which the field represents as `null`.
                            $field->value($object->requiredGender === 0 ? null : $object->requiredGender);
                        }),
                    IntegerFormField::create('requiredPoints')
                        ->label('wcf.acp.user.rank.requiredPoints')
                        ->description('wcf.acp.user.rank.requiredPoints.description')
                        ->addFieldClass('tiny')
                        ->minimum(0)
                        ->value(0)
                        ->saveValueCallback(static function (UserRankBuilder $builder, IntegerFormField $field): void {
                            $builder->setRequiredPoints($field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserRank $object, IFormField $field): void {
                            $field->value($object->requiredPoints);
                        }),
                ])
        ]);
    }
}
