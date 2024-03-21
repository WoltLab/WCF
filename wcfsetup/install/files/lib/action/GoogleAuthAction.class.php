<?php

namespace wcf\action;

use GuzzleHttp\Psr7\Request;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
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
 * Performs authentication against Google (GAIA).
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class GoogleAuthAction extends AbstractOauth2Action
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

    /**
     * @inheritDoc
     */
    public $neededModules = ['GOOGLE_PUBLIC_KEY', 'GOOGLE_PRIVATE_KEY'];

    /**
     * @var array
     */
    private $configuration;

    /**
     * Returns Google's OpenID Connect configuration.
     */
    private function getConfiguration()
    {
        if (!isset($this->configuration)) {
            $request = new Request('GET', 'https://accounts.google.com/.well-known/openid-configuration');
            $response = $this->getHttpClient()->send($request);

            $this->configuration = JSON::decode((string)$response->getBody());
        }

        return $this->configuration;
    }

    /**
     * @inheritDoc
     */
    protected function getTokenEndpoint(): string
    {
        return $this->getConfiguration()['token_endpoint'];
    }

    /**
     * @inheritDoc
     */
    protected function getClientId(): string
    {
        return StringUtil::trim(GOOGLE_PUBLIC_KEY);
    }

    /**
     * @inheritDoc
     */
    protected function getClientSecret(): string
    {
        return StringUtil::trim(GOOGLE_PRIVATE_KEY);
    }

    /**
     * @inheritDoc
     */
    protected function getScope(): string
    {
        return 'profile openid email';
    }

    /**
     * @inheritDoc
     */
    protected function getAuthorizeUrl(): string
    {
        return $this->getConfiguration()['authorization_endpoint'];
    }

    /**
     * @inheritDoc
     */
    protected function getCallbackUrl(): string
    {
        return LinkHandler::getInstance()->getControllerLink(self::class);
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
        $request = new Request('GET', $this->getConfiguration()['userinfo_endpoint'], [
            'accept' => 'application/json',
            'authorization' => \sprintf('Bearer %s', $accessToken['access_token']),
        ]);
        $response = $this->getHttpClient()->send($request);
        $parsed = JSON::decode((string)$response->getBody());

        $parsed['__id'] = $parsed['sub'];
        $parsed['__username'] = $parsed['name'];
        if ($parsed['email']) {
            $parsed['__email'] = $parsed['email'];
        }
        $parsed['accessToken'] = $accessToken;

        return new OauthUser($parsed);
    }

    /**
     * @inheritDoc
     */
    protected function processUser(OauthUser $oauthUser): ResponseInterface
    {
        $user = User::getUserByAuthData('google:' . $oauthUser->getId());

        if ($user->userID) {
            if (WCF::getUser()->userID) {
                // This account belongs to an existing user, but we are already logged in.
                // This can't be handled.

                throw new NamedUserException(
                    WCF::getLanguage()->getDynamicVariable('wcf.user.3rdparty.google.connect.error.inuse')
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
            WCF::getSession()->register('__3rdPartyProvider', 'google');

            if (WCF::getUser()->userID) {
                // This account does not belong to anyone and we are already logged in.
                // Thus we want to connect this account.

                WCF::getSession()->register('__oauthUser', $oauthUser);

                return new RedirectResponse(
                    LinkHandler::getInstance()->getControllerLink(
                        AccountManagementForm::class,
                        [],
                        '#3rdParty'
                    )
                );
            } else {
                // This account does not belong to anyone and we are not logged in.
                // Thus we want to connect this account to a newly registered user.

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
