<?php

namespace wcf\acp\form;

use wcf\data\bbcode\media\provider\BBCodeMediaProvider;
use wcf\data\bbcode\media\provider\BBCodeMediaProviderAction;
use wcf\data\bbcode\media\provider\BBCodeMediaProviderEditor;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\bbcode\media\provider\IBBCodeMediaProvider;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\Regex;
use wcf\util\StringUtil;

/**
 * Shows the BBCode media provider add form.
 *
 * @author      Olaf Braun, Tim Duesterhus
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<BBCodeMediaProvider>
 */
class BBCodeMediaProviderAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.bbcode.mediaProvider.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.bbcode.canManageBBCode'];

    /**
     * @inheritDoc
     */
    public $templateName = 'bbcodeMediaProviderAdd';

    /**
     * @inheritDoc
     */
    public $objectActionClass = BBCodeMediaProviderAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = BBCodeMediaProviderEditForm::class;

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('general')
                ->appendChildren([
                    TextFormField::create('title')
                        ->label('wcf.acp.bbcode.mediaProvider.title')
                        ->required(),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.global.button.disable'),
                    MultilineTextFormField::create('regex')
                        ->label('wcf.acp.bbcode.mediaProvider.regex')
                        ->description('wcf.acp.bbcode.mediaProvider.regex.description')
                        ->required()
                        ->addValidator(
                            new FormFieldValidator('regexValidator', function (MultilineTextFormField $formField) {
                                $lines = \explode("\n", StringUtil::unifyNewlines($formField->getValue()));

                                foreach ($lines as $line) {
                                    if (!Regex::compile($line)->isValid()) {
                                        $formField->addValidationError(
                                            new FormFieldValidationError(
                                                'invalid',
                                                'wcf.acp.bbcode.mediaProvider.regex.error.invalid'
                                            )
                                        );
                                    }
                                }
                            })
                        ),
                    MultilineTextFormField::create('html')
                        ->label('wcf.acp.bbcode.mediaProvider.html')
                        ->description('wcf.acp.bbcode.mediaProvider.html.description')
                        ->addValidator(
                            new FormFieldValidator('emptyValidator', function (MultilineTextFormField $formField) {
                                $classNameFormField = $formField->getDocument()->getFormField('className');

                                if (empty($formField->getValue()) && empty($classNameFormField->getValue())) {
                                    $formField->addValidationError(
                                        new FormFieldValidationError('empty')
                                    );
                                }
                            })
                        ),
                    ClassNameFormField::create('className')
                        ->label('wcf.acp.bbcode.mediaProvider.className')
                        ->implementedInterface(IBBCodeMediaProvider::class)
                ])
        ]);
    }

    #[\Override]
    public function save()
    {
        if ($this->formAction === "create") {
            $this->additionalFields['packageID'] = PACKAGE_ID;
            $this->additionalFields['name'] = 'placeholder_' . StringUtil::getRandomID();
        }

        parent::save();
    }

    #[\Override]
    public function saved()
    {
        if ($this->formAction === "create") {
            /** @var BBCodeMediaProvider $provider */
            $provider = $this->objectAction->getReturnValues()['returnValues'];
            (new BBCodeMediaProviderEditor($provider))->update([
                'name' => 'com.woltlab.wcf.generic' . $provider->providerID,
            ]);
        }

        parent::saved();
    }
}
