<?php

namespace wcf\acp\form;

use wcf\command\notice\CreateNotice;
use wcf\command\notice\UpdateNotice;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\notice\Notice;
use wcf\data\notice\NoticeBuilder;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\ObjectFilterFormField;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\object\filter\builder\NoticeObjectFilterBuilder;
use wcf\system\Regex;

/**
 * Shows the form to create a new notice.
 *
 * @author      Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<Notice, NoticeBuilder>
 */
class NoticeAddForm extends AbstractDatabaseObjectBuilderForm
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
    public string $objectEditLinkController = NoticeEditForm::class;

    #[\Override]
    protected function getDatabaseObjectBuilder(): NoticeBuilder
    {
        if ($this->formObject !== null) {
            return NoticeBuilder::forUpdate($this->formObject);
        }

        return NoticeBuilder::forCreate();
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateNotice($builder);
        }

        return new CreateNotice($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        $this->form->appendChildren([
            FormContainer::create('data')
                ->appendChildren([
                    TextFormField::create('noticeName')
                        ->label('wcf.global.name')
                        ->maximumLength(255)
                        ->required()
                        ->saveValueCallback(static function (NoticeBuilder $builder, IFormField $field) {
                            $builder->setNoticeName($field->getSaveValue());
                        }),
                    MultilineTextFormField::create('notice')
                        ->label('wcf.acp.notice.notice')
                        ->description('wcf.acp.notice.notice.description')
                        ->i18n()
                        ->languageItemPattern('wcf.notice.notice.notice\d+')
                        ->required()
                        ->rows(10)
                        ->saveValueCallback(static function (NoticeBuilder $builder, IFormField $field) {
                            $builder->setNotice($field->getSaveValue());
                        }),
                    BooleanFormField::create('noticeUseHtml')
                        ->label('wcf.acp.notice.noticeUseHtml')
                        ->saveValueCallback(static function (NoticeBuilder $builder, IFormField $field) {
                            $builder->setNoticeUseHtml((bool)$field->getSaveValue());
                        }),
                    IntegerFormField::create('showOrder')
                        ->label('wcf.global.showOrder')
                        ->description('wcf.acp.notice.showOrder.description')
                        ->minimum(0)
                        ->saveValueCallback(static function (NoticeBuilder $builder, IFormField $field) {
                            $builder->setShowOrder($field->getSaveValue());
                        }),
                ]),
            FormContainer::create('settings')
                ->label('wcf.global.settings')
                ->appendChildren([
                    RadioButtonFormField::create('cssClassName')
                        ->label('wcf.acp.notice.cssClassName')
                        ->description('wcf.acp.notice.cssClassName.description')
                        ->options($this->getAvailableCssClassNames())
                        ->value('info')
                        ->required()
                        ->saveValueCallback(static function (NoticeBuilder $builder, IFormField $field) {
                            $cssClassName = $field->getSaveValue();
                            if ($cssClassName === 'custom') {
                                $cssClassName = $field->getDocument()
                                    ->getFormField('customCssClassName')
                                    ->getSaveValue();
                            }

                            $builder->setCssClassName($cssClassName);
                        })
                        ->loadValueCallback(static function (Notice $object, IFormField $field) {
                            $field->value($object->isCustom() ? 'custom' : $object->cssClassName);
                        }),
                    TextFormField::create('customCssClassName')
                        ->label('wcf.acp.notice.customCssClassName')
                        ->maximumLength(255)
                        ->required()
                        ->addValidator(
                            new FormFieldValidator('cssClassName', static function (TextFormField $field) {
                                if (Regex::compile('^-?[_a-zA-Z]+[_a-zA-Z0-9-]+$')->match($field->getValue()) === 0) {
                                    $field->addValidationError(
                                        new FormFieldValidationError(
                                            'invalid',
                                            'wcf.acp.notice.cssClassName.error.invalid'
                                        )
                                    );
                                }
                            })
                        )
                        ->loadValueCallback(static function (Notice $object, IFormField $field) {
                            $field->value($object->isCustom() ? $object->cssClassName : '');
                        }),
                    TemplateFormNode::create('cssClassNameExample')
                        ->templateName('__noticeCssClassNameExample'),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.acp.notice.isDisabled')
                        ->saveValueCallback(static function (NoticeBuilder $builder, IFormField $field) {
                            $builder->setIsDisabled((bool)$field->getSaveValue());
                        }),
                    BooleanFormField::create('isDismissible')
                        ->label('wcf.acp.notice.isDismissible')
                        ->description('wcf.acp.notice.isDismissible.description')
                        ->saveValueCallback(static function (NoticeBuilder $builder, IFormField $field) {
                            $builder->setIsDismissible((bool)$field->getSaveValue());
                        }),
                ]),
            FormContainer::create('conditionsContainer')
                ->label('wcf.acp.notice.conditions')
                ->description('wcf.acp.notice.conditions.description')
                ->appendChild(
                    ObjectFilterFormField::create('conditions')
                        ->builder(new NoticeObjectFilterBuilder())
                        ->saveValueCallback(static function (NoticeBuilder $builder, IFormField $field) {
                            $builder->setConditions($field->getSaveValue());
                        })
                ),
        ]);
    }

    #[\Override]
    protected function finalizeForm(): void
    {
        $this->form->getNodeById('customCssClassName')->addDependency(
            ValueFormFieldDependency::create('cssClassName')
                ->field($this->form->getFormField('cssClassName'))
                ->values(['custom'])
        );
    }

    /**
     * @return array<string, string>
     */
    private function getAvailableCssClassNames(): array
    {
        $options = [];
        foreach (Notice::TYPES as $type) {
            $options[$type] = 'wcf.acp.notice.cssClassName.' . $type;
        }
        $options['custom'] = 'wcf.acp.notice.cssClassName.custom';

        return $options;
    }
}
