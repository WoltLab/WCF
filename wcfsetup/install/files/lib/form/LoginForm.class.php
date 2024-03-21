<?php

namespace wcf\form;

use wcf\system\event\EventHandler;
use wcf\system\user\authentication\event\UserLoggedIn;
use wcf\system\WCF;

/**
 * Shows the user login form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LoginForm extends \wcf\acp\form\LoginForm
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

    #[\Override]
    public function save()
    {
        AbstractForm::save();

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

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'forceLoginRedirect' => FORCE_LOGIN,
        ]);
    }
}
