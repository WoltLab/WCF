<?php

namespace wcf\acp\form;

use Laminas\Diactoros\Uri;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerAction;
use wcf\data\package\update\server\PackageUpdateServerList;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\PasswordFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\UrlFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;

/**
 * Shows the server add form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<PackageUpdateServer>
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

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('data')
                ->appendChildren([
                    UrlFormField::create('serverURL')
                        ->label('wcf.acp.updateServer.serverURL')
                        ->required()
                        ->autoFocus()
                        ->addValidator(
                            new FormFieldValidator('serverUrlValidator', function (UrlFormField $formField) {
                                try {
                                    $url = new Uri($formField->getValue());

                                    if (!$url->getHost()) {
                                        $formField->addValidationError(
                                            new FormFieldValidationError(
                                                'invalid',
                                                'wcf.acp.updateServer.serverURL.error.invalid'
                                            )
                                        );
                                        return;
                                    }
                                    if ($url->getHost() !== 'localhost') {
                                        if ($url->getScheme() !== 'https') {
                                            $formField->addValidationError(
                                                new FormFieldValidationError(
                                                    'invalidScheme',
                                                    'wcf.acp.updateServer.serverURL.error.invalidScheme'
                                                )
                                            );
                                            return;
                                        }
                                        if ($url->getPort()) {
                                            $formField->addValidationError(
                                                new FormFieldValidationError(
                                                    'nonStandardPort',
                                                    'wcf.acp.updateServer.serverURL.error.nonStandardPort'
                                                )
                                            );
                                            return;
                                        }
                                    }
                                    if ($url->getUserInfo()) {
                                        $formField->addValidationError(
                                            new FormFieldValidationError(
                                                'userinfo',
                                                'wcf.acp.updateServer.serverURL.error.userinfo'
                                            )
                                        );
                                        return;
                                    }
                                    if (\str_ends_with(\strtolower($url->getHost()), '.woltlab.com')) {
                                        $formField->addValidationError(
                                            new FormFieldValidationError(
                                                'woltlab',
                                                'wcf.acp.updateServer.serverURL.error.woltlab'
                                            )
                                        );
                                        return;
                                    }
                                } catch (\InvalidArgumentException) {
                                    $formField->addValidationError(
                                        new FormFieldValidationError(
                                            'invalid',
                                            'wcf.acp.updateServer.serverURL.error.invalid'
                                        )
                                    );
                                    return;
                                }

                                if (($duplicate = $this->findDuplicateServer((string)$url))) {
                                    $formField->addValidationError(
                                        new FormFieldValidationError(
                                            'duplicate',
                                            'wcf.acp.updateServer.serverURL.error.duplicate',
                                            ['duplicate' => $duplicate]
                                        )
                                    );
                                    return;
                                }
                            })
                        ),
                    TextFormField::create('loginUsername')
                        ->label('wcf.acp.updateServer.loginUsername')
                        ->description('wcf.acp.updateServer.loginUsername.description')
                        ->addFieldClass('medium'),
                    PasswordFormField::create('loginPassword')
                        ->label('wcf.acp.updateServer.loginPassword')
                        ->description('wcf.acp.updateServer.loginPassword.description')
                        ->addFieldClass('medium')
                        ->passwordStrengthMeter(false)
                        ->autocomplete('off'),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.acp.updateServer.isDisabled'),
                ]),
        ]);
    }

    #[\Override]
    public function finalizeForm()
    {
        parent::finalizeForm();

        $this->form->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                'saveServerProcessor',
                function (IFormDocument $document, array $parameters) {
                    // This ensures that the URL is well-formed.
                    $uri = new Uri($parameters['data']['serverURL']);
                    $parameters['data']['serverURL'] = (string)$uri;

                    return $parameters;
                }
            )
        );
    }

    /**
     * Returns the first package update server with a matching serverURL.
     * @since 5.3
     */
    protected function findDuplicateServer(string $serverURL): ?PackageUpdateServer
    {
        $packageServerList = new PackageUpdateServerList();
        $packageServerList->readObjects();
        foreach ($packageServerList as $packageServer) {
            if ($packageServer->serverURL == $serverURL) {
                return $packageServer;
            }
        }

        return null;
    }
}
