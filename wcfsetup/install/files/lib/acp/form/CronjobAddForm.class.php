<?php

namespace wcf\acp\form;

use Cron\CronExpression;
use Cron\FieldFactory;
use wcf\command\cronjob\CreateCronjob;
use wcf\command\cronjob\UpdateCronjob;
use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobBuilder;
use wcf\data\DatabaseObjectBuilder;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\cronjob\ICronjob;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;

/**
 * Shows the cronjob add form.
 *
 * @author      Olaf Braun, Alexander Ebert
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<Cronjob, CronjobBuilder>
 */
class CronjobAddForm extends AbstractDatabaseObjectBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cronjob.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canManageCronjob'];

    public string $objectEditLinkController = CronjobEditForm::class;

    #[\Override]
    protected function getDatabaseObjectBuilder(): CronjobBuilder
    {
        if ($this->formObject !== null) {
            return CronjobBuilder::forUpdate($this->formObject);
        }

        return CronjobBuilder::forCreate()
            ->setPackageID(\PACKAGE_ID);
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateCronjob($builder);
        }

        return new CreateCronjob($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('generalContainer')
                ->appendChildren([
                    ClassNameFormField::create('className')
                        ->label('wcf.acp.cronjob.className')
                        ->implementedInterface(ICronjob::class)
                        ->required()
                        ->saveValueCallback(static function (CronjobBuilder $builder, ClassNameFormField $field): void {
                            $builder->setClassName($field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Cronjob $object, IFormField $field): void {
                            $field->value($object->className);
                        }),
                    TextFormField::create('description')
                        ->label('wcf.acp.cronjob.description')
                        ->required()
                        ->l10n()
                        ->maximumLength(255)
                        ->saveValueCallback(static function (CronjobBuilder $builder, TextFormField $field): void {
                            $builder->setDescription($field->getL10nValues());
                        })
                        ->loadValueCallback(static function (Cronjob $object, IFormField $field): void {
                            $field->value($object->getL10nValues('description'));
                        }),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.global.button.disable')
                        ->saveValueCallback(static function (CronjobBuilder $builder, BooleanFormField $field): void {
                            $builder->setIsDisabled((bool)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Cronjob $object, IFormField $field): void {
                            $field->value($object->isDisabled);
                        }),
                ]),
            FormContainer::create('timingContainer')
                ->label('wcf.acp.cronjob.timing')
                ->appendChildren([
                    TextFormField::create('startMinute')
                        ->label('wcf.acp.cronjob.startMinute')
                        ->description('wcf.acp.cronjob.startMinute.description')
                        ->addFieldClass('short')
                        ->value('*')
                        ->addValidator(self::getTimeFormFieldValidator())
                        ->required()
                        ->saveValueCallback(static function (CronjobBuilder $builder, TextFormField $field): void {
                            $builder->setStartMinute($field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Cronjob $object, IFormField $field): void {
                            $field->value($object->startMinute);
                        }),
                    TextFormField::create('startHour')
                        ->label('wcf.acp.cronjob.startHour')
                        ->description('wcf.acp.cronjob.startHour.description')
                        ->addFieldClass('short')
                        ->value('*')
                        ->addValidator(self::getTimeFormFieldValidator())
                        ->required()
                        ->saveValueCallback(static function (CronjobBuilder $builder, TextFormField $field): void {
                            $builder->setStartHour($field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Cronjob $object, IFormField $field): void {
                            $field->value($object->startHour);
                        }),
                    TextFormField::create('startDom')
                        ->label('wcf.acp.cronjob.startDom')
                        ->description('wcf.acp.cronjob.startDom.description')
                        ->addFieldClass('short')
                        ->value('*')
                        ->addValidator(self::getTimeFormFieldValidator())
                        ->required()
                        ->saveValueCallback(static function (CronjobBuilder $builder, TextFormField $field): void {
                            $builder->setStartDom($field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Cronjob $object, IFormField $field): void {
                            $field->value($object->startDom);
                        }),
                    TextFormField::create('startMonth')
                        ->label('wcf.acp.cronjob.startMonth')
                        ->description('wcf.acp.cronjob.startMonth.description')
                        ->addFieldClass('short')
                        ->value('*')
                        ->addValidator(self::getTimeFormFieldValidator())
                        ->required()
                        ->saveValueCallback(static function (CronjobBuilder $builder, TextFormField $field): void {
                            $builder->setStartMonth($field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Cronjob $object, IFormField $field): void {
                            $field->value($object->startMonth);
                        }),
                    TextFormField::create('startDow')
                        ->label('wcf.acp.cronjob.startDow')
                        ->description('wcf.acp.cronjob.startDow.description')
                        ->addFieldClass('short')
                        ->value('*')
                        ->addValidator(self::getTimeFormFieldValidator())
                        ->required()
                        ->saveValueCallback(static function (CronjobBuilder $builder, TextFormField $field): void {
                            $builder->setStartDow($field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Cronjob $object, IFormField $field): void {
                            $field->value($object->startDow);
                        }),
                ]),
        ]);
    }

    /**
     * @since 6.3
     */
    public static function getTimeFormFieldValidator(): FormFieldValidator
    {
        return new FormFieldValidator(
            'format',
            static function (TextFormField $formField) {
                $fieldFactory = new FieldFactory();
                $position = match ($formField->getId()) {
                    'startMinute' => CronExpression::MINUTE,
                    'startHour' => CronExpression::HOUR,
                    'startDom' => CronExpression::DAY,
                    'startMonth' => CronExpression::MONTH,
                    'startDow' => CronExpression::WEEKDAY,
                };

                if (!$fieldFactory->getField($position)->validate($formField->getValue())) {
                    $formField->addValidationError(
                        new FormFieldValidationError(
                            'format',
                            "wcf.acp.pip.cronjob.{$formField->getId()}.error.format"
                        )
                    );
                }
            }
        );
    }

    /**
     * @deprecated 6.3 Use `getTimeFormFieldValidator()` instead.
     */
    public static function getTimeFormFiledValidator(): FormFieldValidator
    {
        return self::getTimeFormFieldValidator();
    }
}
