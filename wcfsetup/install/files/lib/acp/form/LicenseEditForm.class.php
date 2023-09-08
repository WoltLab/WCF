<?php

namespace wcf\acp\form;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
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
use wcf\system\io\HttpFactory;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

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

    private array $apiResponse;

    private string $url;

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
    }

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

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
                                $this->apiResponse = $this->getLicenseData($licenseNo->getValue(), $serialNo->getValue());
                            } catch (ConnectException) {
                                $serialNo->addValidationError(new FormFieldValidationError(
                                    'failedConnect',
                                    'wcf.acp.firstTimeSetup.license.credentials.error.failedConnect'
                                ));
                            } catch (ClientExceptionInterface | MappingError) {
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

    private function getLicenseData(string $licenseNo, string $serialNo): array
    {
        $request = new Request(
            'POST',
            'https://api.woltlab.com/2.0/customer/license/list.json',
            [
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            \http_build_query([
                'licenseNo' => $licenseNo,
                'serialNo' => $serialNo,
                'instanceId' => \hash_hmac('sha256', 'api.woltlab.com', \WCF_UUID),
            ], '', '&', \PHP_QUERY_RFC1738)
        );

        $response = HttpFactory::makeClientWithTimeout(5)->send($request);

        return (new MapperBuilder())
            ->allowSuperfluousKeys()
            ->mapper()
            ->map(
                <<<'EOT'
                    array {
                        status: 200,
                        license: array {
                            authCode: string,
                            type: string,
                            expiryDates?: array<string, int>,
                        },
                        pluginstore: array<string, string>,
                        woltlab: array<string, string>,
                    }
                    EOT,
                Source::json($response->getBody())
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

        if (isset($this->apiResponse)) {
            $optionData = [
                Option::getOptionByName('package_server_auth_code')->optionID => $this->apiResponse['license']['authCode'],
            ];
            $objectAction = new OptionAction(
                [],
                'updateAll',
                [
                    'data' => $optionData,
                ]
            );
            $objectAction->executeAction();
        }

        $this->saved();

        if (isset($this->url)) {
            return new RedirectResponse($this->url);
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'url' => $this->url,
        ]);
    }
}
