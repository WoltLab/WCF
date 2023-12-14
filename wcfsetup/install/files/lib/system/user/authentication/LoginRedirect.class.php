<?php

namespace wcf\system\user\authentication;

use wcf\system\application\ApplicationHandler;
use wcf\system\request\LinkHandler;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Manages the URL to which the user should be redirected after login.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class LoginRedirect
{
    public const SESSION_VAR_KEY = 'login__redirect__url';

    public static function setUrl(string $url): void
    {
        // Discard URL if it is not an absolute URL of local content.
        if (!ApplicationHandler::getInstance()->isInternalURL($url)) {
            return;
        }

        WCF::getSession()->register(self::SESSION_VAR_KEY, $url);
    }

    public static function getUrl(): string
    {
        $url = WCF::getSession()->getVar(self::SESSION_VAR_KEY);
        if (!$url) {
            if (RequestHandler::getInstance()->isACPRequest()) {
                $application = ApplicationHandler::getInstance()->getActiveApplication();

                return $application->getPageURL() . 'acp/';
            }

            return LinkHandler::getInstance()->getLink();
        }

        WCF::getSession()->unregister(self::SESSION_VAR_KEY);

        return $url;
    }
}
