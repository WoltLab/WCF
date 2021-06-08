<?php

namespace wcf\system\package\plugin;

use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;

/**
 * Abstract implementation of a package installation plugin deleting a certain type of templates.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Package\Plugin
 * @since   5.5
 */
abstract class AbstractTemplateDeletePackageInstallationPlugin extends AbstractFileDeletePackageInstallationPlugin
{
    /**
     * @inheritDoc
     */
    public $tagName = 'template';

    /**
     * @inheritDoc
     */
    protected function getFilenameTableColumn(): string
    {
        return 'templateName';
    }

    /**
     * @inheritDoc
     */
    protected function addFormFields(IFormDocument $form)
    {
        parent::addFormFields($form);

        /** @var TextFormField $templateFormField */
        $templateFormField = $form->getNodeById($this->tagName);
        $templateFormField->addValidator(new FormFieldValidator('tplSuffix', static function (TextFormField $formField) {
            if (\substr($formField->getValue(), -4) === '.tpl') {
                $formField->addValidationError(new FormFieldValidationError(
                    'tplSuffix',
                    'wcf.acp.pip.acpTemplateDelete.template.error.tplSuffix'
                ));
            }
        }));
    }
}
