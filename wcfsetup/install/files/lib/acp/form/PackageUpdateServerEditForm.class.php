<?php

namespace wcf\acp\form;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\RedirectResponse;
use wcf\acp\page\PackageUpdateServerListPage;
use wcf\data\IStorableObject;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\PasswordFormField;
use wcf\system\form\builder\field\UrlFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\interaction\admin\PackageUpdateServerInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the server edit form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PackageUpdateServerEditForm extends PackageUpdateServerAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.package.server.list';

    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        try {
            $queryParameters = Helper::mapQueryParameters(
                $_GET,
                <<<'EOT'
                    array {
                        id: positive-int
                    }
                    EOT
            );
            $this->formObject = new PackageUpdateServer($queryParameters['id']);

            if (!$this->formObject->getObjectID()) {
                throw new IllegalLinkException();
            }
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        if ($this->formObject->isWoltLabUpdateServer() || $this->formObject->isWoltLabStoreServer()) {
            return new RedirectResponse(
                LinkHandler::getInstance()->getControllerLink(LicenseEditForm::class),
            );
        }
    }

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $serverUrlField = $this->form->getFormField('serverURL');
        \assert($serverUrlField instanceof UrlFormField);
        $serverUrlField->immutable();
        // The server URL cannot be modified, thus we do not need to validate it.
        $serverUrlField->removeValidator('serverUrlValidator');

        if ($this->formObject->loginUsername) {
            $passwordField = $this->form->getFormField('loginPassword');
            \assert($passwordField instanceof PasswordFormField);
            $passwordField->placeholder('wcf.acp.updateServer.loginPassword.noChange');
        }
    }

    #[\Override]
    public function finalizeForm()
    {
        parent::finalizeForm();

        $this->form->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                'editServerProcessor',
                function (IFormDocument $document, array $parameters) {
                    if ($parameters['data']['loginUsername'] === $this->formObject->loginUsername && !$parameters['data']['loginPassword']) {
                        unset($parameters['data']['loginUsername']);
                        unset($parameters['data']['loginPassword']);
                    }

                    return $parameters;
                },
                function (IFormDocument $document, array $data, IStorableObject $object) {
                    unset($data['loginPassword']);

                    return $data;
                }
            )
        );
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new PackageUpdateServerInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(PackageUpdateServerListPage::class)
            ),
        ]);
    }
}
