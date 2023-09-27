<?php

namespace wcf\acp\form;

use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Client\ClientExceptionInterface;
use wcf\data\option\Option;
use wcf\data\option\OptionAction;
use wcf\data\package\update\server\PackageUpdateServerAction;
use wcf\data\package\update\server\PackageUpdateServerList;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\CheckboxFormField;
use wcf\system\form\builder\field\dependency\EmptyFormFieldDependency;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\package\license\exception\ParsingFailed;
use wcf\system\package\license\LicenseApi;
use wcf\system\request\LinkHandler;
use wcf\util\HeaderUtil;

/**
 * Sets up license data during first time setup.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class FirstTimeSetupLicenseForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.configuration.package.canEditServer'];

    private LicenseApi $licenseApi;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (\FIRST_TIME_SETUP_STATE == -1) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

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
                        ->placeholder('123456'),
                    TextFormField::create('serialNo')
                        ->label('wcf.acp.package.update.serialNo')
                        ->required()
                        ->maximumLength(40)
                        ->addFieldClass('medium')
                        ->placeholder('XXXX-XXXX-XXXX-XXXX-XXXX')
                        ->addValidator(new FormFieldValidator('serialNo', function (TextFormField $serialNo) {
                            $licenseNo = $serialNo->getDocument()->getNodeById('licenseNo');
                            \assert($licenseNo instanceof TextFormField);

                            try {
                                $this->licenseApi = LicenseApi::fetchFromRemote([
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
            FormContainer::create('noCredentials')
                ->label('wcf.acp.firstTimeSetup.license.noCredentials')
                ->appendChildren([
                    CheckboxFormField::create('noCredentialsConfirm')
                        ->label('wcf.acp.firstTimeSetup.license.noCredentialsConfirm')
                        ->description('wcf.acp.firstTimeSetup.license.noCredentialsConfirm.description'),
                ]),
        ]);

        $credentialsContainer->addDependency(
            EmptyFormFieldDependency::create('noCredentialsConfirm')
                ->fieldId('noCredentialsConfirm')
        );
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        $data = $this->form->getData();

        if (!$data['data']['noCredentialsConfirm']) {
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
                            'loginUsername' => $data['data']['licenseNo'],
                            'loginPassword' => $data['data']['serialNo'],
                        ],
                    ]
                );
                $objectAction->executeAction();
            }
        }

        $optionData = [
            Option::getOptionByName('first_time_setup_state')->optionID => 1,
        ];

        if (isset($this->licenseApi)) {
            $this->licenseApi->updateLicenseFile();

            if (isset($this->licenseApi->getData()->license['authCode'])) {
                $optionData[Option::getOptionByName('package_server_auth_code')->optionID] = $this->licenseApi->getData()->license['authCode'];
            }
        }

        $objectAction = new OptionAction(
            [],
            'updateAll',
            [
                'data' => $optionData,
            ]
        );
        $objectAction->executeAction();

        $this->saved();

        \http_response_code(303);
        HeaderUtil::redirect(LinkHandler::getInstance()->getControllerLink(
            FirstTimeSetupAction::class,
        ));

        exit;
    }
}
