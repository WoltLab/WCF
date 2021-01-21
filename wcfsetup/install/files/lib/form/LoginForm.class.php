<?php

namespace wcf\form;

use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the user login form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Form
 */
class LoginForm extends \wcf\acp\form\LoginForm
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        if (FORCE_LOGIN) {
            WCF::getSession()->unregister('__wsc_forceLoginRedirect');
        }

        // change user
        $needsMultifactor = WCF::getSession()->changeUserAfterMultifactorAuthentication($this->user);

        $this->saved();

        // redirect to url
        WCF::getTPL()->assign('__hideUserMenu', true);

        $this->performRedirect($needsMultifactor);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'loginController' => LinkHandler::getInstance()->getLink('Login'),
            'forceLoginRedirect' => (FORCE_LOGIN && WCF::getSession()->getVar('__wsc_forceLoginRedirect') !== null),

            /** @deprecated 5.4 - The values below should no longer be used. */
            'useCookies' => 0,
            'supportsPersistentLogins' => false,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function performRedirect(bool $needsMultifactor = false)
    {
        if (empty($this->url) || \mb_stripos($this->url, '?login/') !== false || \mb_stripos($this->url, '/login/') !== false) {
            $this->url = LinkHandler::getInstance()->getLink();
        }

        parent::performRedirect($needsMultifactor);
    }
}
