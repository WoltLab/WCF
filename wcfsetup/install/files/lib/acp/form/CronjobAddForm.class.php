<?php

namespace wcf\acp\form;

use Cron\CronExpression;
use Cron\FieldFactory;
use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobAction;
use wcf\data\cronjob\CronjobEditor;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\cronjob\ICronjob;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\language\I18nHandler;

/**
 * Shows the cronjob add form.
 *
 * @author      Olaf Braun, Alexander Ebert
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<Cronjob>
 */
class CronjobAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cronjob.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canManageCronjob'];

    /**
     * @inheritDoc
     */
    public $objectActionClass = CronjobAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = CronjobEditForm::class;

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('generalContainer')
                ->appendChildren([
                    ClassNameFormField::create('className')
                        ->label('wcf.acp.cronjob.className')
                        ->implementedInterface(ICronjob::class)
                        ->required(),
                    TextFormField::create('description')
                        ->label('wcf.acp.cronjob.description')
                        ->required()
                        ->i18n()
                        ->languageItemPattern('wcf.acp.cronjob.description.cronjob\d+'),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.global.button.disable'),
                ]),
            FormContainer::create('timingContainer')
                ->label('wcf.acp.cronjob.timing')
                ->appendChildren([
                    TextFormField::create('startMinute')
                        ->label('wcf.acp.cronjob.startMinute')
                        ->description('wcf.acp.cronjob.startMinute.description')
                        ->addFieldClass('short')
                        ->value('*')
                        ->addValidator($this->getTimeFormFiledValidator())
                        ->required(),
                    TextFormField::create('startHour')
                        ->label('wcf.acp.cronjob.startHour')
                        ->description('wcf.acp.cronjob.startHour.description')
                        ->addFieldClass('short')
                        ->value('*')
                        ->addValidator($this->getTimeFormFiledValidator())
                        ->required(),
                    TextFormField::create('startDom')
                        ->label('wcf.acp.cronjob.startDom')
                        ->description('wcf.acp.cronjob.startDom.description')
                        ->addFieldClass('short')
                        ->value('*')
                        ->addValidator($this->getTimeFormFiledValidator())
                        ->required(),
                    TextFormField::create('startMonth')
                        ->label('wcf.acp.cronjob.startMonth')
                        ->description('wcf.acp.cronjob.startMonth.description')
                        ->addFieldClass('short')
                        ->value('*')
                        ->addValidator($this->getTimeFormFiledValidator())
                        ->required(),
                    TextFormField::create('startDow')
                        ->label('wcf.acp.cronjob.startDow')
                        ->description('wcf.acp.cronjob.startDow.description')
                        ->addFieldClass('short')
                        ->value('*')
                        ->addValidator($this->getTimeFormFiledValidator())
                        ->required(),
                ]),
        ]);
    }

    public static function getTimeFormFiledValidator(): FormFieldValidator
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

    #[\Override]
    public function save()
    {
        if ($this->formAction === 'create') {
            $this->additionalFields['packageID'] = PACKAGE_ID;
            $this->additionalFields['cronjobName'] = 'com.woltlab.wcf.cronjob';
        }

        parent::save();
    }

    #[\Override]
    public function saved()
    {
        $updateData = [];
        $formData = $this->form->getData();

        if ($this->formAction === 'create') {
            $cronjob = $this->objectAction->getReturnValues()['returnValues'];
            \assert($cronjob instanceof Cronjob);

            // update `cronjobName`
            $updateData['cronjobName'] = 'com.woltlab.wcf.cronjob' . $cronjob->cronjobID;
        } else {
            $cronjob = $this->formObject;
        }

        $languageItem = "wcf.acp.cronjob.description.cronjob{$cronjob->cronjobID}";
        if (isset($formData['description_i18n'])) {
            $updateData['description'] = $languageItem;
            I18nHandler::getInstance()->save(
                $formData['description_i18n'],
                $languageItem,
                'wcf.acp.cronjob',
                $cronjob->packageID
            );
        } elseif ($cronjob->description === $languageItem) {
            I18nHandler::getInstance()->remove($cronjob->description);
        }

        $cronjobEditor = new CronjobEditor($cronjob);
        $cronjobEditor->update($updateData);

        parent::saved();
    }
}
