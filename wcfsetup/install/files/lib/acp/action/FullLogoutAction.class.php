<?php

namespace wcf\acp\action;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\action\AbstractSecureAction;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Does a full user logout in the admin control panel (deleting the session).
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Action
 */
class FullLogoutAction extends LogoutAction
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        AbstractSecureAction::execute();

        WCF::getSession()->delete();

        $this->executed();

        return new RedirectResponse(
            LinkHandler::getInstance()->getLink()
        );
    }
}
