<?php

namespace wcf\acp\form;

use wcf\data\template\group\TemplateGroup;
use wcf\data\template\group\TemplateGroupAction;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Shows the form for adding new template groups.
 *
 * @author      Olaf Braun, Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<TemplateGroup>
 */
class TemplateGroupAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.template.group.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.template.canManageTemplate'];

    /**
     * @inheritDoc
     */
    public $objectActionClass = TemplateGroupAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = TemplateGroupEditForm::class;

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $availableTemplateGroups = TemplateGroup::getSelectList([-1], 1);

        $this->form->appendChildren([
            FormContainer::create('general')
                ->appendChildren([
                    SelectFormField::create('parentTemplateGroupID')
                        ->label('wcf.acp.template.group.parentTemplateGroup')
                        ->options($availableTemplateGroups)
                        ->available(\count($availableTemplateGroups) > 0),
                    TextFormField::create('templateGroupName')
                        ->label('wcf.global.name')
                        ->required()
                        ->addValidator(TemplateGroupAddForm::getTemplateNameValidator($this->formObject)),
                    TextFormField::create('templateGroupFolderName')
                        ->label('wcf.acp.template.group.folderName')
                        ->required()
                        ->addValidator(TemplateGroupAddForm::getFolderNameValidator())
                        ->addValidator(TemplateGroupAddForm::getUniqueFolderNameValidator($this->formObject)),
                ])
        ]);
    }

    public static function getFolderNameValidator(): FormFieldValidator
    {
        return new FormFieldValidator('folderNameValidator', function (TextFormField $formField) {
            $formField->value(FileUtil::addTrailingSlash($formField->getValue()));

            if (!\preg_match('/^[a-z0-9_\- ]+\/$/i', $formField->getValue())) {
                $formField->addValidationError(
                    new FormFieldValidationError(
                        'invalid',
                        'wcf.acp.template.group.folderName.error.invalid'
                    )
                );
            }
        });
    }

    public static function getUniqueFolderNameValidator(?TemplateGroup $formObject = null): FormFieldValidator
    {
        return new FormFieldValidator(
            'uniqueFolderNameValidator',
            function (TextFormField $formField) use ($formObject) {
                $formField->value(FileUtil::addTrailingSlash($formField->getValue()));

                if ($formField->getValue() === $formObject?->templateGroupFolderName) {
                    return;
                }

                $sql = "SELECT  COUNT(*)
                        FROM    wcf1_template_group
                        WHERE   templateGroupFolderName = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$formField->getValue()]);

                if ($statement->fetchSingleColumn() > 0) {
                    $formField->addValidationError(
                        new FormFieldValidationError(
                            'notUnique',
                            'wcf.acp.template.group.folderName.error.notUnique'
                        )
                    );
                }
            }
        );
    }

    public static function getTemplateNameValidator(?TemplateGroup $formObject = null): FormFieldValidator
    {
        return new FormFieldValidator(
            'templateNameValidator',
            function (TextFormField $formField) use ($formObject) {
                if ($formField->getValue() === $formObject?->templateGroupName) {
                    return;
                }

                $sql = "SELECT  COUNT(*)
                        FROM    wcf1_template_group
                        WHERE   templateGroupName = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$formField->getValue()]);

                if ($statement->fetchSingleColumn() > 0) {
                    $formField->addValidationError(
                        new FormFieldValidationError(
                            'notUnique',
                            'wcf.acp.template.group.name.error.notUnique'
                        )
                    );
                }
            }
        );
    }
}
