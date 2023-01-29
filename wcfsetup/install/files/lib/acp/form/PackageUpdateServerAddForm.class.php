<?php

namespace wcf\acp\form;

use Laminas\Diactoros\Uri;
use wcf\data\IStorableObject;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerAction;
use wcf\data\package\update\server\PackageUpdateServerList;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\dependency\NonEmptyFormFieldDependency;
use wcf\system\form\builder\field\PasswordFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\UrlFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;

/**
 * Shows the server add form.
 *
 * @property    PackageUpdateServerAction   $objectAction
 *
 * @author  Florian Gail, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PackageUpdateServerAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.package.server.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.configuration.package.canEditServer'];

    /**
     * @inheritDoc
     */
    public $objectActionClass = PackageUpdateServerAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = PackageUpdateServerEditForm::class;

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

        $passwordPlaceholder = '';
        if (
            $this->formAction !== IFormDocument::FORM_MODE_CREATE
            && $this->formObject instanceof PackageUpdateServer
            && $this->formObject->loginUsername !== ''
        ) {
            $passwordPlaceholder = 'wcf.acp.updateServer.loginPassword.noChange';
        }

        $this->form->appendChildren([
            FormContainer::create('data')
                ->appendChildren([
                    UrlFormField::create('serverURL')
                        ->label('wcf.acp.updateServer.serverURL')
                        ->required()
                        ->maximumLength(255)
                        ->immutable($this->formAction !== IFormDocument::FORM_MODE_CREATE)
                        ->addValidator(new FormFieldValidator('valid', function (UrlFormField $formField) {
                            if ($formField->getValidationErrors() !== []) {
                                return;
                            }

                            try {
                                $url = new Uri($formField->getValue());

                                if (!$url->getHost()) {
                                    $formField->addValidationError(new FormFieldValidationError(
                                        'invalid',
                                        'wcf.acp.updateServer.serverURL.error.invalid',
                                    ));
                                }
                                if ($url->getHost() !== 'localhost') {
                                    if ($url->getScheme() !== 'https') {
                                        $formField->addValidationError(new FormFieldValidationError(
                                            'invalidScheme',
                                            'wcf.acp.updateServer.serverURL.error.invalidScheme',
                                        ));
                                    }
                                    if ($url->getPort()) {
                                        $formField->addValidationError(new FormFieldValidationError(
                                            'nonStandardPort',
                                            'wcf.acp.updateServer.serverURL.error.nonStandardPort',
                                        ));
                                    }
                                }
                                if ($url->getUserInfo()) {
                                    $formField->addValidationError(new FormFieldValidationError(
                                        'userinfo',
                                        'wcf.acp.updateServer.serverURL.error.userinfo',
                                    ));
                                }
                                if (\str_ends_with(\strtolower($url->getHost()), '.woltlab.com')) {
                                    $formField->addValidationError(new FormFieldValidationError(
                                        'woltlab',
                                        'wcf.acp.updateServer.serverURL.error.woltlab',
                                    ));
                                }
                            } catch (\InvalidArgumentException) {
                                $formField->addValidationError(new FormFieldValidationError(
                                    'invalid',
                                    'wcf.acp.updateServer.serverURL.error.invalid',
                                ));
                            }

                            if (
                                ($duplicate = $this->getDuplicateServer((string)$url))
                                && (
                                    $this->formAction === IFormDocument::FORM_MODE_CREATE
                                    || (
                                        $this->formObject instanceof PackageUpdateServer
                                        && $this->formObject->getObjectID() !== $duplicate->getObjectID()
                                    )
                                )
                            ) {
                                $formField->addValidationError(new FormFieldValidationError(
                                    'duplicate',
                                    'wcf.acp.updateServer.serverURL.error.duplicate',
                                    [
                                        'duplicate' => $duplicate,
                                    ]
                                ));
                            }
                        })),
                    TextFormField::create('loginUsername')
                        ->label('wcf.acp.updateServer.loginUsername')
                        ->description('wcf.acp.updateServer.loginUsername.description')
                        ->maximumLength(255),
                    PasswordFormField::create('loginPassword')
                        ->label('wcf.acp.updateServer.loginPassword')
                        ->description('wcf.acp.updateServer.loginPassword.description')
                        ->placeholder($passwordPlaceholder)
                        ->maximumLength(255)
                        ->addDependency(
                            NonEmptyFormFieldDependency::create('loginUsername')
                                ->fieldId('loginUsername')
                        ),
                ]),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function buildForm()
    {
        parent::buildForm();

        $this->form->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'password',
            function (IFormDocument $document, array $parameters) {
                if ($this->formAction === IFormDocument::FORM_MODE_CREATE) {
                    return $parameters;
                }

                $username = $parameters['data']['loginUsername'];
                \assert($this->formObject instanceof PackageUpdateServer);

                if ($username === '') {
                    $parameters['data']['loginUsername'] = '';
                    $parameters['data']['loginPassword'] = '';
                } elseif (
                    $username === $this->formObject->loginUsername
                    && $parameters['data']['loginPassword'] === ''
                ) {
                    unset($parameters['data']['loginPassword']);
                }

                return $parameters;
            },
            static function (IFormDocument $document, array $data, IStorableObject $object) {
                \assert($object instanceof PackageUpdateServer);

                $data['loginPassword'] = '';

                return $data;
            }
        ));
    }

    /**
     * Returns the first package update server with a matching serverURL.
     *
     * @since       6.0
     */
    protected function getDuplicateServer(string $serverUrl): ?PackageUpdateServer
    {
        $packageServerList = new PackageUpdateServerList();
        $packageServerList->readObjects();
        foreach ($packageServerList as $packageServer) {
            if ($packageServer->serverURL == $serverUrl) {
                return $packageServer;
            }
        }

        return null;
    }
}
