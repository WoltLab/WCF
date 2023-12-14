<?php

namespace wcf\form;

use wcf\system\event\EventHandler;
use wcf\system\user\authentication\event\UserLoggedIn;
use wcf\system\WCF;

/**
 * Shows the user login form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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

        if (!$needsMultifactor) {
            EventHandler::getInstance()->fire(
                new UserLoggedIn($this->user)
            );
        }

        $this->saved();

        $this->performRedirect($needsMultifactor);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'forceLoginRedirect' => (FORCE_LOGIN && WCF::getSession()->getVar('__wsc_forceLoginRedirect') !== null),
        ]);
    }
}
