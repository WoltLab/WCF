<?php

namespace wcf\form;

use wcf\data\object\type\ObjectType;
use wcf\data\user\User;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\event\UserLoggedIn;
use wcf\system\user\multifactor\IMultifactorMethod;
use wcf\system\user\multifactor\Setup;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Represents the multifactor authentication form.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Form
 * @since   5.4
 */
class MultifactorAuthenticationForm extends AbstractFormBuilderForm
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

    /**
     * @inheritDoc
     */
    public $formAction = 'authenticate';

    /**
     * @var User
     */
    private $user;

    /**
     * @var Setup[]
     */
    private $setups;

    /**
     * @var ObjectType
     */
    private $method;

    /**
     * @var IMultifactorMethod
     */
    private $processor;

    /**
     * @var Setup
     */
    private $setup;

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
        }

        if (WCF::getUser()->userID) {
            $this->performRedirect();
        }

        $this->user = WCF::getSession()->getPendingUserChange();
        if (!$this->user) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable(
                'wcf.user.security.multifactor.authentication.noPendingUserChange'
            ));
        }

        $this->setups = Setup::getAllForUser($this->user);

        if (empty($this->setups)) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable(
                'wcf.user.security.multifactor.authentication.noSetup',
                [
                    'user' => $this->user,
                ]
            ));
        }

        \uasort($this->setups, static function (Setup $a, Setup $b) {
            return $b->getObjectType()->priority <=> $a->getObjectType()->priority;
        });

        $setupId = \array_keys($this->setups)[0];
        if (isset($_GET['id'])) {
            $setupId = \intval($_GET['id']);
        }

        if (!isset($this->setups[$setupId])) {
            throw new IllegalLinkException();
        }

        $this->setup = $this->setups[$setupId];
        $this->method = $this->setup->getObjectType();
        \assert($this->method->getDefinition()->definitionName === 'com.woltlab.wcf.multifactor');

        $this->processor = $this->method->getProcessor();
    }

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChild(
            TemplateFormNode::create('loginAs')
                ->templateName('__multifactorAuthenticationLoginAs')
        );

        $this->processor->createAuthenticationForm($this->form, $this->setup);
    }

    public function save()
    {
        AbstractForm::save();

        WCF::getDB()->beginTransaction();

        $setup = $this->setup->lock();

        $this->processor->processAuthenticationForm($this->form, $setup);

        WCF::getDB()->commitTransaction();

        WCF::getSession()->applyPendingUserChange($this->user);
        EventHandler::getInstance()->fire(
            new UserLoggedIn($this->user)
        );

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
     * Returns to the redirectUrl if given and to the landing page otherwise.
     */
    protected function performRedirect()
    {
        if ($this->redirectUrl) {
            HeaderUtil::redirect($this->redirectUrl);
        } else {
            HeaderUtil::redirect(LinkHandler::getInstance()->getLink());
        }

        exit;
    }

    /**
     * @inheritDoc
     */
    protected function setFormAction()
    {
        $this->form->action(LinkHandler::getInstance()->getControllerLink(static::class, [
            'object' => $this->setup,
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
            'setups' => $this->setups,
            'user' => $this->user,
            'userProfile' => UserProfileRuntimeCache::getInstance()->getObject($this->user->userID),
            'setup' => $this->setup,
            'redirectUrl' => $this->redirectUrl,
        ]);
    }
}
