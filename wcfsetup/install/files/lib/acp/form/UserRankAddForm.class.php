<?php

namespace wcf\acp\form;

use wcf\data\IStorableObject;
use wcf\data\user\group\UserGroup;
use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankAction;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\BadgeColorFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\FileProcessorFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;

/**
 * Shows the user rank add form.
 *
 * @author      Olaf Braun, Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<UserRank>
 */
class UserRankAddForm extends AbstractFormBuilderForm
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
    public $objectActionClass = UserRankAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = UserRankEditForm::class;

    #[\Override]
    public function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('section')
                ->appendChildren([
                    TextFormField::create('rankTitle')
                        ->label('wcf.acp.user.rank.title')
                        ->i18n()
                        ->languageItemPattern('wcf.user.rank.\w+')
                        ->required(),
                    BadgeColorFormField::create('cssClassName')
                        ->label('wcf.acp.user.rank.cssClassName')
                        ->description('wcf.acp.user.rank.cssClassName.description')
                        ->textReferenceNodeId('rankTitle')
                        ->defaultLabelText(WCF::getLanguage()->get('wcf.acp.user.rank.title'))
                        ->required()
                ]),
            FormContainer::create('imageContainer')
                ->label('wcf.acp.user.rank.image')
                ->appendChildren([
                    FileProcessorFormField::create('rankImageFileID')
                        ->objectType('com.woltlab.wcf.user.rank.image')
                        ->label('wcf.acp.user.rank.image')
                        ->singleFileUpload()
                        ->bigPreview(),
                    IntegerFormField::create('repeatImage')
                        ->label('wcf.acp.user.rank.repeatImage')
                        ->description('wcf.acp.user.rank.repeatImage.description')
                        ->addFieldClass('tiny')
                        ->minimum(1)
                        ->value(1),
                    BooleanFormField::create('hideTitle')
                        ->label('wcf.acp.user.rank.hideTitle')
                        ->description('wcf.acp.user.rank.hideTitle.description')
                        ->value(false)
                ]),
            FormContainer::create('requirementsContainer')
                ->label('wcf.acp.user.rank.requirement')
                ->appendChildren([
                    SingleSelectionFormField::create('groupID')
                        ->label('wcf.user.group')
                        ->description('wcf.acp.user.rank.userGroup.description')
                        ->options(UserGroup::getSortedGroupsByType([], [UserGroup::GUESTS, UserGroup::EVERYONE]))
                        ->required(),
                    SelectFormField::create('requiredGender')
                        ->label('wcf.user.option.gender')
                        ->description('wcf.acp.user.rank.requiredGender.description')
                        ->options([
                            1 => 'wcf.user.gender.male',
                            2 => 'wcf.user.gender.female',
                            3 => 'wcf.user.gender.other'
                        ]),
                    IntegerFormField::create('requiredPoints')
                        ->label('wcf.acp.user.rank.requiredPoints')
                        ->description('wcf.acp.user.rank.requiredPoints.description')
                        ->addFieldClass('tiny')
                        ->minimum(0)
                        ->value(0),
                ])
        ]);
    }

    #[\Override]
    protected function finalizeForm()
    {
        parent::finalizeForm();

        $this->form->getDataHandler()
            ->addProcessor(
                new CustomFormDataProcessor(
                    'requiredGenderProcessor',
                    function (IFormDocument $document, array $parameters) {
                        $parameters['data']['requiredGender'] = $parameters['data']['requiredGender'] ?: 0;

                        return $parameters;
                    },
                    function (IFormDocument $document, array $data, IStorableObject $object) {
                        \assert($object instanceof UserRank);

                        $data['requiredGender'] = $data['requiredGender'] ?: null;

                        return $data;
                    }
                )
            );
    }
}
