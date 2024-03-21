<?php

namespace wcf\action;

use GuzzleHttp\Psr7\Request;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Client\ClientExceptionInterface;
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
 * Performs authentication against GitHub.com
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class GithubAuthAction extends AbstractOauth2Action
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

    /**
     * @inheritDoc
     */
    public $neededModules = ['GITHUB_PUBLIC_KEY', 'GITHUB_PRIVATE_KEY'];

    /**
     * @inheritDoc
     */
    protected function getTokenEndpoint(): string
    {
        return 'https://github.com/login/oauth/access_token';
    }

    /**
     * @inheritDoc
     */
    protected function getClientId(): string
    {
        return StringUtil::trim(GITHUB_PUBLIC_KEY);
    }

    /**
     * @inheritDoc
     */
    protected function getClientSecret(): string
    {
        return StringUtil::trim(GITHUB_PRIVATE_KEY);
    }

    /**
     * @inheritDoc
     */
    protected function getScope(): string
    {
        return 'user:email';
    }

    /**
     * @inheritDoc
     */
    protected function getAuthorizeUrl(): string
    {
        return 'https://github.com/login/oauth/authorize';
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
        $request = new Request('GET', 'https://api.github.com/user', [
            'accept' => 'application/json',
            'authorization' => \sprintf('Bearer %s', $accessToken['access_token']),
        ]);
        $response = $this->getHttpClient()->send($request);
        $parsed = JSON::decode((string)$response->getBody());

        $parsed['__id'] = $parsed['id'];
        $parsed['__username'] = $parsed['login'];
        $parsed['accessToken'] = $accessToken;

        return new OauthUser($parsed);
    }

    /**
     * @inheritDoc
     */
    protected function processUser(OauthUser $oauthUser): ResponseInterface
    {
        $user = User::getUserByAuthData('github:' . $oauthUser->getId());

        if ($user->userID) {
            if (WCF::getUser()->userID) {
                // This account belongs to an existing user, but we are already logged in.
                // This can't be handled.

                throw new NamedUserException(
                    WCF::getLanguage()->getDynamicVariable('wcf.user.3rdparty.github.connect.error.inuse')
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
            WCF::getSession()->register('__3rdPartyProvider', 'github');

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

                try {
                    $request = new Request('GET', 'https://api.github.com/user/emails', [
                        'accept' => 'application/json',
                        'authorization' => \sprintf('Bearer %s', $oauthUser["accessToken"]["access_token"]),
                    ]);
                    $response = $this->getHttpClient()->send($request);
                    $emails = JSON::decode((string)$response->getBody());

                    // search primary email
                    $email = $emails[0]['email'];
                    foreach ($emails as $tmp) {
                        if ($tmp['primary']) {
                            $email = $tmp['email'];
                            break;
                        }
                    }
                    $oauthUser["__email"] = $email;
                } catch (ClientExceptionInterface $e) {
                }

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
