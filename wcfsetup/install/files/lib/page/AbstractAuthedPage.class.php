<?php

namespace wcf\page;

use wcf\data\user\User;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * Automatically authes the user for the current request via an access-token.
 * A missing token will be ignored, an invalid token results in a throw of a IllegalLinkException.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.1 Use `AllowAccessToken` instead.
 */
abstract class AbstractAuthedPage extends AbstractPage
{
    /**
     * If “Force login” is active, this page is faked as available during offline mode
     * in order to bypass the CheckForForceLogin middleware.
     */
    public const AVAILABLE_DURING_OFFLINE_MODE = \FORCE_LOGIN;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (\OFFLINE !== 0) {
            throw new IllegalLinkException();
        }

        // check security token
        $this->checkAccessToken();

        if (\FORCE_LOGIN !== 0 && WCF::getUser()->isGuest()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Validates the access-token and performs the login.
     *
     * @return void
     */
    protected function checkAccessToken()
    {
        if (isset($_REQUEST['at'])) {
            if (\preg_match('~^(?P<userID>\d{1,10})-(?P<token>[a-f0-9]{40})$~', $_REQUEST['at'], $matches)) {
                $userID = (int)$matches['userID'];
                $token = $matches['token'];

                if (!WCF::getUser()->isGuest()) {
                    if ($userID === WCF::getUser()->userID && \hash_equals(WCF::getUser()->accessToken, $token)) {
                        // everything is fine, but we are already logged in
                        return;
                    } else {
                        // token is invalid
                        throw new IllegalLinkException();
                    }
                } else {
                    $user = new User($userID);
                    if (
                        !$user->isGuest() && $user->accessToken !== '' && \hash_equals(
                            $user->accessToken,
                            $token
                        ) && $user->banned === 0
                    ) {
                        // token is valid and user is not banned -> change user
                        SessionHandler::getInstance()->changeUser($user, true);
                    } else {
                        // token is invalid
                        throw new IllegalLinkException();
                    }
                }
            } else {
                throw new IllegalLinkException();
            }
        }
    }
}
