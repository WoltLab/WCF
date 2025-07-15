<?php

namespace wcf\acp\form;

use wcf\data\notice\Notice;
use wcf\data\notice\NoticeAction;
use wcf\data\notice\NoticeList;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\condition\provider\combined\NoticeConditionProvider;
use wcf\system\form\builder\container\condition\ConditionFormContainer;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\CssClassNameFormField;
use wcf\system\form\builder\field\dependency\NonEmptyFormFieldDependency;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\ShowOrderFormField;
use wcf\system\form\builder\field\TextFormField;

/**
 * Shows the form to create a new notice.
 *
 * @author Olaf Braun, Matthias Schmidt
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<Notice>
 */
class NoticeAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.notice.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.notice.canManageNotice'];

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = NoticeEditForm::class;

    /**
     * @inheritDoc
     */
    public $objectActionClass = NoticeAction::class;

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('generalSection')
                ->appendChildren([
                    TextFormField::create('noticeName')
                        ->label('wcf.global.name')
                        ->required(),
                    MultilineTextFormField::create('notice')
                        ->i18n()
                        ->languageItemPattern('wcf.notice.notice.notice\d+')
                        ->required(),
                    BooleanFormField::create('noticeUseHtml')
                        ->label('wcf.acp.notice.noticeUseHtml'),
                    ShowOrderFormField::create()
                        ->description('wcf.acp.notice.showOrder.description')
                        ->options($this->getNotices()),
                ]),
            FormContainer::create('settingsSection')
                ->label('wcf.global.settings')
                ->appendChildren([
                    CssClassNameFormField::create('cssClassName')
                        ->label('wcf.acp.notice.cssClassName')
                        ->visualTemplate('<woltlab-core-notice type="{$className}">{$label}</woltlab-core-notice>')
                        ->description('wcf.acp.notice.cssClassName.description')
                        ->options($this->getClassNames())
                        ->supportCustomClassName()
                        ->required(),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.acp.notice.isDisabled'),
                    BooleanFormField::create('isDismissible')
                        ->label('wcf.acp.notice.isDismissible')
                        ->description('wcf.acp.notice.isDismissible.description'),
                    BooleanFormField::create('resetIsDismissed')
                        ->label('wcf.acp.notice.resetIsDismissed')
                        ->description('wcf.acp.notice.resetIsDismissed.description')
                        ->available($this->formAction === 'edit')
                        ->addDependency(
                            NonEmptyFormFieldDependency::create('isDismissibleDependency')
                                ->fieldId('isDismissible')
                        ),
                ]),
            ConditionFormContainer::create()
                ->conditionProvider(new NoticeConditionProvider()),
        ]);
    }

    /**
     * @return Notice[]
     */
    private function getNotices(): array
    {
        $optionList = new NoticeList();
        $optionList->sqlOrderBy = "showOrder ASC";
        $optionList->readObjects();

        return $optionList->getObjects();
    }

    /**
     * @return array<string, string>
     */
    private function getClassNames(): array
    {
        $classNames = [];

        foreach (Notice::TYPES as $type) {
            $classNames[$type] = 'wcf.acp.notice.cssClassName.' . $type;
        }

        return $classNames;
    }
}
