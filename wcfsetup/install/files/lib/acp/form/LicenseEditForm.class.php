<?php

namespace wcf\acp\form;

use GuzzleHttp\Exception\ConnectException;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Client\ClientExceptionInterface;
use wcf\data\option\Option;
use wcf\data\option\OptionAction;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerAction;
use wcf\data\package\update\server\PackageUpdateServerList;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\application\ApplicationHandler;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\CheckboxFormField;
use wcf\system\form\builder\field\dependency\EmptyFormFieldDependency;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\package\license\exception\ParsingFailed;
use wcf\system\package\license\LicenseApi;
use wcf\system\package\license\LicenseData;
use wcf\system\request\LinkHandler;

/**
 * Set up or edit the license data.
 *
 * @author Tim Duesterhus, Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class LicenseEditForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.package';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.configuration.package.canEditServer'];

    /**
     * @inheritDoc
     */
    public $templateName = 'licenseEdit';

    private LicenseApi $licenseApi;

    private LicenseData $licenseData;

    private string $url;

    private bool $failedValidation = false;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        $url = $_GET['url'] ?? '';
        if ($url && ApplicationHandler::getInstance()->isInternalURL($url)) {
            $this->url = $url;
        }

        if (isset($_GET['failedValidation'])) {
            $this->failedValidation = true;
        }
    }

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

        $this->licenseApi = new LicenseApi();

        $licenseNo = '';
        $serialNo = '';
        $authData = PackageUpdateServer::getWoltLabUpdateServer()->getAuthData();
        if (!empty($authData['username']) && !empty($authData['password'])) {
            $licenseNo = $authData['username'];
            $serialNo = $authData['password'];
        }

        $this->form->appendChildren([
            $credentialsContainer = FormContainer::create('credentials')
                ->label('wcf.acp.firstTimeSetup.license.credentials')
                ->description('wcf.acp.firstTimeSetup.license.explanation')
                ->appendChildren([
                    TextFormField::create('licenseNo')
                        ->label('wcf.acp.package.update.licenseNo')
                        ->description('wcf.acp.firstTimeSetup.license.credentials.customerArea')
                        ->required()
                        ->maximumLength(12)
                        ->addFieldClass('short')
                        ->value($licenseNo)
                        ->placeholder('123456'),
                    TextFormField::create('serialNo')
                        ->label('wcf.acp.package.update.serialNo')
                        ->required()
                        ->maximumLength(40)
                        ->addFieldClass('medium')
                        ->value($serialNo)
                        ->placeholder('XXXX-XXXX-XXXX-XXXX-XXXX')
                        ->addValidator(new FormFieldValidator('serialNo', function (TextFormField $serialNo) {
                            $licenseNo = $serialNo->getDocument()->getNodeById('licenseNo');
                            \assert($licenseNo instanceof TextFormField);

                            try {
                                $this->licenseData = $this->licenseApi->fetchFromRemote([
                                    'username' => $licenseNo->getValue(),
                                    'password' => $serialNo->getValue(),
                                ]);
                            } catch (ConnectException) {
                                $serialNo->addValidationError(new FormFieldValidationError(
                                    'failedConnect',
                                    'wcf.acp.firstTimeSetup.license.credentials.error.failedConnect'
                                ));
                            } catch (ClientExceptionInterface | ParsingFailed) {
                                $serialNo->addValidationError(new FormFieldValidationError(
                                    'failedValidation',
                                    'wcf.acp.firstTimeSetup.license.credentials.error.failedValidation'
                                ));
                            }
                        })),
                ]),

        ]);
        $this->form->successMessage('wcf.global.success.edit');

        if ($licenseNo) {
            $this->form->appendChildren([
                FormContainer::create('noCredentials')
                    ->label('wcf.acp.license.noCredentials')
                    ->appendChildren([
                        CheckboxFormField::create('noCredentialsConfirm')
                            ->label('wcf.acp.license.noCredentialsConfirm'),
                    ]),
            ]);

            $credentialsContainer->addDependency(
                EmptyFormFieldDependency::create('noCredentialsConfirm')
                    ->fieldId('noCredentialsConfirm')
            );
        }

        if ($this->failedValidation) {
            $formField = $this->form->getNodeById('serialNo');
            if ($formField instanceof TextFormField) {
                $formField->addValidationError(new FormFieldValidationError(
                    'failedValidation',
                    'wcf.acp.firstTimeSetup.license.credentials.error.failedValidation'
                ));
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function setFormAction()
    {
        if (!isset($this->url)) {
            parent::setFormAction();
            return;
        }

        $this->form->action(
            LinkHandler::getInstance()->getControllerLink(static::class, [
                'url' => $this->url,
            ])
        );
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        $data = $this->form->getData();

        $loginUsername = '';
        $loginPassword = '';
        if (empty($data['data']['noCredentialsConfirm'])) {
            $loginUsername = $data['data']['licenseNo'];
            $loginPassword = $data['data']['serialNo'];
        }

        $packageServerList = new PackageUpdateServerList();
        $packageServerList->readObjects();

        foreach ($packageServerList as $packageServer) {
            if (
                !$packageServer->isWoltLabUpdateServer()
                && !$packageServer->isWoltLabStoreServer()
            ) {
                continue;
            }

            $objectAction = new PackageUpdateServerAction(
                [$packageServer],
                'update',
                [
                    'data' => [
                        'loginUsername' => $loginUsername,
                        'loginPassword' => $loginPassword,
                    ],
                ]
            );
            $objectAction->executeAction();
        }

        $authCode = '';
        if (isset($this->licenseData)) {
            $this->licenseApi->updateLicenseFile($this->licenseData);

            $authCode = $this->licenseData->license['authCode'] ?? '';
        } else {
            $this->licenseApi->clearLicenseFile();
        }

        $objectAction = new OptionAction(
            [],
            'updateAll',
            [
                'data' => [
                    Option::getOptionByName('package_server_auth_code')->optionID => $authCode
                ],
            ]
        );
        $objectAction->executeAction();

        $this->saved();

        if (isset($this->url)) {
            return new RedirectResponse($this->url);
        }
    }
}
