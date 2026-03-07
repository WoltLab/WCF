<?php

namespace wcf\acp\form;

use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeAction;
use wcf\data\reaction\type\ReactionTypeList;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ShowOrderFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\UploadFormField;

/**
 * Represents the reaction type add form.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 *
 * @extends AbstractFormBuilderForm<ReactionType>
 */
class ReactionTypeAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $formAction = 'create';

    /**
     * @inheritDoc
     */
    public $objectActionClass = ReactionTypeAction::class;

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
    public $objectEditLinkController = ReactionTypeEditForm::class;

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

        $dataContainer = FormContainer::create('generalSection')
            ->appendChildren([
                TitleFormField::create()
                    ->required()
                    ->autoFocus()
                    ->maximumLength(255)
                    ->i18n()
                    ->languageItemPattern('wcf.reactionType.title\d+'),
                ShowOrderFormField::create()
                    ->required()
                    ->options($this->getReactionTypes()),
                BooleanFormField::create('isAssignable')
                    ->label('wcf.acp.reactionType.isAssignable')
                    ->description('wcf.acp.reactionType.isAssignable.description')
                    ->value(true),
            ]);

        $iconContainer = FormContainer::create('imageSection')
            ->label('wcf.acp.reactionType.image')
            ->appendChildren([
                UploadFormField::create('iconFile')
                    ->label('wcf.acp.reactionType.image')
                    ->required()
                    ->maximum(1)
                    ->imageOnly(true)
                    ->allowSvgImage(true),
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

        return \array_map(static fn($option) => $option->getTitle(), $list->getObjects());
    }
}
