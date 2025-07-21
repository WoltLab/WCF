<?php

namespace wcf\acp\form;

use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyAction;
use wcf\data\trophy\TrophyList;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\condition\provider\UserConditionProvider;
use wcf\system\exception\NamedUserException;
use wcf\system\form\builder\container\condition\ConditionFormContainer;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\RowFormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ColorFormField;
use wcf\system\form\builder\field\dependency\NonEmptyFormFieldDependency;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\DescriptionFormField;
use wcf\system\form\builder\field\FileProcessorFormField;
use wcf\system\form\builder\field\IconFormField;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\ShowOrderFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\WCF;
use wcf\util\HtmlString;

/**
 * Represents the trophy add form.
 *
 * @author Olaf Braun, Joshua Ruesweg
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @extends AbstractFormBuilderForm<Trophy>
 */
class TrophyAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.trophy.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.trophy.canManageTrophy'];

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_TROPHY'];

    /**
     * @inheritDoc
     */
    public $objectActionClass = TrophyAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = TrophyEditForm::class;

    #[\Override]
    public function createForm()
    {
        parent::createForm();

        $categories = TrophyCategoryCache::getInstance()->getCategories();
        if ($categories === []) {
            throw new NamedUserException(HtmlString::fromSafeHtml(WCF::getLanguage()->getDynamicVariable('wcf.acp.trophy.error.noCategories')));
        }

        $this->form->appendChildren([
            FormContainer::create('generalContainer')
                ->appendChildren([
                    TitleFormField::create()
                        ->i18n()
                        ->languageItemPattern('wcf.user.trophy.title\d+')
                        ->required(),
                    DescriptionFormField::create()
                        ->i18n()
                        ->languageItemPattern('wcf.user.trophy.description\d+'),
                    BooleanFormField::create('trophyUseHtml')
                        ->label('wcf.acp.trophy.trophyUseHtml')
                        ->value(false),
                    SingleSelectionFormField::create('categoryID')
                        ->options($categories)
                        ->label('wcf.global.category')
                        ->filterable(\count($categories) > 20)
                        ->required(),
                    ShowOrderFormField::create()
                        ->options(new TrophyList())
                        ->description('wcf.acp.trophy.showOrder.description')
                        ->required(),
                    BooleanFormField::create('isDisabled')
                        ->value(false)
                        ->label('wcf.acp.trophy.isDisabled'),
                    BooleanFormField::create('awardAutomatically')
                        ->value(false)
                        ->label('wcf.acp.trophy.awardAutomatically'),
                    BooleanFormField::create('revokeAutomatically')
                        ->value(false)
                        ->label('wcf.acp.trophy.revokeAutomatically')
                        ->addDependency(
                            NonEmptyFormFieldDependency::create('awardAutomaticallyDependency')
                                ->fieldId('awardAutomatically')
                        ),
                    RadioButtonFormField::create('type')
                        ->label('wcf.acp.trophy.type')
                        ->value(Trophy::TYPE_BADGE)
                        ->required()
                        ->options([
                            Trophy::TYPE_IMAGE => 'wcf.acp.trophy.type.imageUpload',
                            Trophy::TYPE_BADGE => 'wcf.acp.trophy.type.badge',
                        ]),
                ]),
            FormContainer::create('imageUploadContainer')
                ->label('wcf.acp.trophy.type.imageUpload')
                ->appendChildren([
                    FileProcessorFormField::create('imageFileID')
                        ->label('wcf.acp.trophy.type.imageUpload')
                        ->description('wcf.acp.trophy.type.imageUpload.description')
                        ->objectType('com.woltlab.wcf.trophy')
                        ->singleFileUpload()
                        ->simpleReplace()
                        ->bigPreview()
                        ->hideDeleteButton()
                        ->required(),
                ])
                ->addDependency(
                    ValueFormFieldDependency::create('typeDependency')
                        ->fieldId('type')
                        ->values([Trophy::TYPE_IMAGE])
                ),
            RowFormContainer::create('badgeContainer')
                ->addClass('section')
                ->label('wcf.acp.trophy.type.badge')
                ->appendChildren([
                    IconFormField::create('iconName')
                        ->addClasses(['col-xs-12', 'col-md-4'])
                        ->label('wcf.acp.trophy.type.badge')
                        ->value('trophy;false')
                        ->required(),
                    ColorFormField::create('iconColor')
                        ->label('wcf.acp.trophy.badge.iconColor')
                        ->addClasses(['col-xs-12', 'col-md-4'])
                        ->value('rgba(255, 255, 255, 1)')
                        ->required(),
                    ColorFormField::create('badgeColor')
                        ->label('wcf.acp.trophy.badge.badgeColor')
                        ->addClasses(['col-xs-12', 'col-md-4'])
                        ->value('rgba(50, 92, 132, 1)')
                        ->required(),
                ])
                ->addDependency(
                    ValueFormFieldDependency::create('typeDependency')
                        ->fieldId('type')
                        ->values([Trophy::TYPE_BADGE])
                ),
            // TODO make it required
            ConditionFormContainer::create()
                ->label('wcf.acp.trophy.conditions')
                ->description('wcf.acp.trophy.conditions.description')
                ->conditionProvider(new UserConditionProvider())
                ->addDependency(
                    NonEmptyFormFieldDependency::create('awardAutomaticallyDependency')
                        ->fieldId('awardAutomatically')
                ),
        ]);
    }
}
