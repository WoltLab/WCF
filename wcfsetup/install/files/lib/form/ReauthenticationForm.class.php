<?php

namespace wcf\form;

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
 * @package WoltLabSuite\Core\Form
 * @since   5.4
 */
class ReauthenticationForm extends AbstractFormBuilderForm
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

    /**
     * @inheritDoc
     */
    public $formAction = 'authenticate';

    /**
     * @var string
     */
    public $redirectUrl;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_GET['url']) && ApplicationHandler::getInstance()->isInternalURL($_GET['url'])) {
            $this->redirectUrl = $_GET['url'];
        } else {
            throw new IllegalLinkException();
        }

        if (!WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }

        if (!WCF::getSession()->needsReauthentication()) {
            $this->performRedirect();
        }
    }

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

        $this->form->markRequiredFields(false);
        $this->form->appendChildren([
            TemplateFormNode::create('loginAs')
                ->templateName('__reauthenticationLoginAs'),
            UserPasswordField::create()
                ->required(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        WCF::getSession()->registerReauthentication();

        $this->saved();
    }

    /**
     * @inheritDoc
     */
    public function saved()
    {
        AbstractForm::saved();

        $this->performRedirect();
    }

    /**
     * Returns to the redirectUrl.
     */
    protected function performRedirect()
    {
        HeaderUtil::redirect($this->redirectUrl);

        exit;
    }

    /**
     * @inheritDoc
     */
    protected function setFormAction()
    {
        $this->form->action(LinkHandler::getInstance()->getControllerLink(static::class, [
            'url' => $this->redirectUrl,
        ]));
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'redirectUrl' => $this->redirectUrl,
        ]);
    }
}
