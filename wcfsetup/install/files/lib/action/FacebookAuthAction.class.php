<?php

namespace wcf\action;

use GuzzleHttp\Psr7\Request;
use Laminas\Diactoros\Response\RedirectResponse;
use wcf\data\user\User;
use wcf\form\AccountManagementForm;
use wcf\form\RegisterForm;
use wcf\system\event\EventHandler;
use wcf\system\exception\NamedUserException;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\event\UserLoggedIn;
use wcf\system\user\authentication\oauth\User as OauthUser;
use wcf\system\WCF;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Performs authentication against Facebook
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Action
 */
final class FacebookAuthAction extends AbstractOauth2Action
{
    /**
     * @inheritDoc
     */
    public $neededModules = ['FACEBOOK_PUBLIC_KEY', 'FACEBOOK_PRIVATE_KEY'];

    /**
     * @inheritDoc
     */
    protected function getTokenEndpoint(): string
    {
        return 'https://graph.facebook.com/oauth/access_token';
    }

    /**
     * @inheritDoc
     */
    protected function getClientId(): string
    {
        return StringUtil::trim(FACEBOOK_PUBLIC_KEY);
    }

    /**
     * @inheritDoc
     */
    protected function getClientSecret(): string
    {
        return StringUtil::trim(FACEBOOK_PRIVATE_KEY);
    }

    /**
     * @inheritDoc
     */
    protected function getScope(): string
    {
        return 'email';
    }

    /**
     * @inheritDoc
     */
    protected function getAuthorizeUrl(): string
    {
        return 'https://www.facebook.com/dialog/oauth';
    }

    /**
     * @inheritDoc
     */
    protected function getCallbackUrl(): string
    {
        $callbackURL = LinkHandler::getInstance()->getControllerLink(self::class);

        // Work around Facebook performing an illegal substitution of the Slash
        // by '%2F' when entering redirect URI (RFC 3986 sect. 2.2, sect. 3.4)
        $callbackURL = \preg_replace_callback('/(?<=\?).*/', static function ($matches) {
            return \rawurlencode($matches[0]);
        }, $callbackURL);

        return $callbackURL;
    }

    /**
     * @inheritDoc
     */
    protected function supportsState(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function getUser(array $accessToken): OauthUser
    {
        $request = new Request('GET', 'https://graph.facebook.com/me?fields=email,id,name', [
            'accept' => 'application/json',
            'authorization' => \sprintf('Bearer %s', $accessToken['access_token']),
        ]);
        $response = $this->getHttpClient()->send($request);
        $parsed = JSON::decode((string)$response->getBody());

        $parsed['__id'] = $parsed['id'];
        $parsed['__username'] = $parsed['name'];
        if (!empty($parsed['email'])) {
            $parsed['__email'] = $parsed['email'];
        }
        $parsed['accessToken'] = $accessToken;

        return new OauthUser($parsed);
    }

    /**
     * @inheritDoc
     */
    protected function processUser(OauthUser $oauthUser)
    {
        $user = User::getUserByAuthData('facebook:' . $oauthUser->getId());

        if ($user->userID) {
            if (WCF::getUser()->userID) {
                // This account belongs to an existing user, but we are already logged in.
                // This can't be handled.

                throw new NamedUserException(
                    WCF::getLanguage()->getDynamicVariable('wcf.user.3rdparty.facebook.connect.error.inuse')
                );
            } else {
                // This account belongs to an existing user, we are not logged in.
                // Perform the login.

                WCF::getSession()->changeUser($user);
                WCF::getSession()->update();
                EventHandler::getInstance()->fire(
                    new UserLoggedIn($user)
                );

                return new RedirectResponse(
                    LinkHandler::getInstance()->getLink()
                );
            }
        } else {
            WCF::getSession()->register('__3rdPartyProvider', 'facebook');

            if (WCF::getUser()->userID) {
                // This account does not belong to anyone, and we are already logged in.
                // Thus, we want to connect this account.

                WCF::getSession()->register('__oauthUser', $oauthUser);

                return new RedirectResponse(
                    LinkHandler::getInstance()->getControllerLink(
                        AccountManagementForm::class,
                        [],
                        '#3rdParty'
                    )
                );
            } else {
                // This account does not belong to anyone, and we are not logged in.
                // Thus, we want to connect this account to a newly registered user.

                WCF::getSession()->register('__oauthUser', $oauthUser);
                WCF::getSession()->register('__username', $oauthUser->getUsername());
                WCF::getSession()->register('__email', $oauthUser->getEmail());

                // We assume that bots won't register an external account first, so
                // we skip the captcha.
                WCF::getSession()->register('noRegistrationCaptcha', true);

                WCF::getSession()->update();

                return new RedirectResponse(
                    LinkHandler::getInstance()->getControllerLink(RegisterForm::class)
                );
            }
        }
    }
}
