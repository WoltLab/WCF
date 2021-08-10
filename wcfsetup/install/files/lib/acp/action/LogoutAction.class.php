<?php

namespace wcf\acp\action;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\action\AbstractSecureAction;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Does the user logout in the admin control panel (clearing reauthentication).
 *
 * @author  Tim Duesterhus, Marcel Werk
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Action
 */
class LogoutAction extends AbstractSecureAction
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        WCF::getSession()->clearReauthentication();

        $this->executed();

        return new RedirectResponse(
            LinkHandler::getInstance()->getLink()
        );
    }
}
