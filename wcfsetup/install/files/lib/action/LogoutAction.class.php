<?php

namespace wcf\action;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Does the user logout.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Action
 */
class LogoutAction extends \wcf\acp\action\LogoutAction
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

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
