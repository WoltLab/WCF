<?php

namespace wcf\page;

use wcf\data\user\User;
use wcf\system\exception\IllegalLinkException;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * Automatically authes the user for the current request via an access-token.
 * A missing token will be ignored, an invalid token results in a throw of a IllegalLinkException.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Page
 */
abstract class AbstractAuthedPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        // check security token
        $this->checkAccessToken();
    }

    /**
     * Validates the access-token and performs the login.
     */
    protected function checkAccessToken()
    {
        if (isset($_REQUEST['at'])) {
            if (\preg_match('~^(?P<userID>\d{1,10})-(?P<token>[a-f0-9]{40})$~', $_REQUEST['at'], $matches)) {
                $userID = $matches['userID'];
                $token = $matches['token'];

                if (WCF::getUser()->userID) {
                    if ($userID == WCF::getUser()->userID && \hash_equals(WCF::getUser()->accessToken, $token)) {
                        // everything is fine, but we are already logged in
                        return;
                    } else {
                        // token is invalid
                        throw new IllegalLinkException();
                    }
                } else {
                    $user = new User($userID);
                    if (
                        $user->userID && $user->accessToken && \hash_equals(
                            $user->accessToken,
                            $token
                        ) && !$user->banned
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
