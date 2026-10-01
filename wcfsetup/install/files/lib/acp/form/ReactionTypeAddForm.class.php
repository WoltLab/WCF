<?php

namespace wcf\acp\form;

use wcf\command\reaction\type\CreateReactionType;
use wcf\command\reaction\type\UpdateReactionType;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeBuilder;
use wcf\data\reaction\type\ReactionTypeList;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\FileProcessorFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\ShowOrderFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\util\StringUtil;

/**
 * Represents the reaction type add form.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 *
 * @extends AbstractDatabaseObjectBuilderForm<ReactionType, ReactionTypeBuilder>
 */
class ReactionTypeAddForm extends AbstractDatabaseObjectBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.reactionType.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.reaction.canManageReactionType'];

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_LIKE'];

    /**
     * @inheritDoc
     */
    public string $objectEditLinkController = ReactionTypeEditForm::class;

    #[\Override]
    protected function getDatabaseObjectBuilder(): ReactionTypeBuilder
    {
        if ($this->formObject !== null) {
            return ReactionTypeBuilder::forUpdate($this->formObject);
        }

        return ReactionTypeBuilder::forCreate();
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateReactionType($builder);
        }

        return new CreateReactionType($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        $dataContainer = FormContainer::create('generalSection')
            ->appendChildren([
                TitleFormField::create()
                    ->required()
                    ->autoFocus()
                    ->maximumLength(255)
                    ->l10n()
                    ->saveValueCallback(static function (ReactionTypeBuilder $builder, TitleFormField $field) {
                        $builder->setTitle($field->getL10nValues());
                    })
                    ->loadValueCallback(static function (ReactionType $object, IFormField $field) {
                        $field->value($object->getL10nValues('title'));
                    }),
                ShowOrderFormField::create()
                    ->required()
                    ->options($this->getReactionTypes(), labelLanguageItems: false)
                    ->saveValueCallback(static function (ReactionTypeBuilder $builder, ShowOrderFormField $field) {
                        $builder->setShowOrder($field->getSaveValue());
                    })
                    ->loadValueCallback(static function (ReactionType $object, IFormField $field) {
                        $field->value($object->showOrder);
                    }),
                BooleanFormField::create('isAssignable')
                    ->label('wcf.acp.reactionType.isAssignable')
                    ->description('wcf.acp.reactionType.isAssignable.description')
                    ->value(true)
                    ->saveValueCallback(static function (ReactionTypeBuilder $builder, IFormField $field) {
                        $builder->setIsAssignable((bool)$field->getSaveValue());
                    })
                    ->loadValueCallback(static function (ReactionType $object, IFormField $field) {
                        $field->value($object->isAssignable);
                    }),
            ]);

        $iconContainer = FormContainer::create('imageSection')
            ->label('wcf.acp.reactionType.image')
            ->appendChildren([
                FileProcessorFormField::create('iconFileID')
                    ->objectType('com.woltlab.wcf.reactionType.icon')
                    ->label('wcf.acp.reactionType.image')
                    ->required()
                    ->singleFileUpload()
                    ->bigPreview()
                    ->saveValueCallback(static function (ReactionTypeBuilder $builder, FileProcessorFormField $field) {
                        $builder->setIconFileID($field->getSaveValue());
                    })
                    ->loadValueCallback(static function (ReactionType $object, IFormField $field) {
                        if ($object->iconFileID !== null) {
                            $field->value($object->iconFileID);
                        }
                    }),
            ]);

        $this->form->appendChildren([
            $dataContainer,
            $iconContainer,
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function getReactionTypes(): array
    {
        $list = new ReactionTypeList();
        $list->readObjects();

        return \array_map(static fn($option) => StringUtil::encodeHTML($option->getTitle()), $list->getObjects());
    }
}
