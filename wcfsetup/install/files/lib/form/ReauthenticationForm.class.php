<?php

namespace wcf\form;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\user\UserPasswordField;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Represents the reauthentication form.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 *
 * @extends AbstractFormBuilderForm<null>
 */
class ReauthenticationForm extends AbstractFormBuilderForm
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

    /**
     * @inheritDoc
     */
    public $formAction = 'authenticate';

    /**
     * @var ?string
     */
    public $redirectUrl;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_GET['url']) && ApplicationHandler::getInstance()->isInternalURL($_GET['url'])) {
            $this->redirectUrl = $_GET['url'];
        } else {
            throw new IllegalLinkException();
        }

        if (WCF::getUser()->isGuest()) {
            throw new PermissionDeniedException();
        }

        if (!WCF::getSession()->needsReauthentication()) {
            return $this->getRedirectResponse();
        }
    }

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            TemplateFormNode::create('loginAs')
                ->templateName('__reauthenticationLoginAs'),
            UserPasswordField::create()
                ->required()
                ->autocomplete('current-password')
                ->autoFocus(),
        ]);
    }

    #[\Override]
    public function save()
    {
        AbstractForm::save();

        WCF::getSession()->registerReauthentication();

        $this->saved();
    }

    #[\Override]
    public function saved()
    {
        AbstractForm::saved();

        $this->setPsr7Response($this->getRedirectResponse());
    }

    /**
     * @return void
     * @deprecated 5.5 Use `getRedirectResponse()` and the PSR-7 layer instead.
     */
    protected function performRedirect()
    {
        HeaderUtil::redirect($this->redirectUrl);

        exit;
    }

    /**
     * Returns a response redirecting to the redirectUrl.
     *
     * @see ReauthenticationForm::$redirectUrl
     * @since 5.5
     */
    protected function getRedirectResponse(): ResponseInterface
    {
        return new RedirectResponse($this->redirectUrl);
    }

    #[\Override]
    protected function setFormAction()
    {
        $this->form->action(LinkHandler::getInstance()->getControllerLink(static::class, [
            'url' => $this->redirectUrl,
        ]));
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'redirectUrl' => $this->redirectUrl,
        ]);
    }
}
