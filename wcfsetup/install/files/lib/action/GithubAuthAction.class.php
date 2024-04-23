<?php

namespace wcf\action;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\oauth\User as OauthUser;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Performs authentication against GitHub.com
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class GithubAuthAction extends AbstractOauth2AuthAction
{
    #[\Override]
    protected function getTokenEndpoint(): string
    {
        return 'https://github.com/login/oauth/access_token';
    }

    #[\Override]
    protected function getClientId(): string
    {
        return StringUtil::trim(GITHUB_PUBLIC_KEY);
    }

    #[\Override]
    protected function getClientSecret(): string
    {
        return StringUtil::trim(GITHUB_PRIVATE_KEY);
    }

    #[\Override]
    protected function getScope(): string
    {
        return 'user:email';
    }

    #[\Override]
    protected function getAuthorizeUrl(): string
    {
        return 'https://github.com/login/oauth/authorize';
    }

    #[\Override]
    protected function getCallbackUrl(): string
    {
        return LinkHandler::getInstance()->getControllerLink(self::class);
    }

    #[\Override]
    protected function supportsState(): bool
    {
        return true;
    }

    #[\Override]
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

    #[\Override]
    protected function getProviderName(): string
    {
        return 'github';
    }

    #[\Override]
    protected function redirectToRegistration(OauthUser $oauthUser): ResponseInterface
    {
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

        return parent::redirectToRegistration($oauthUser);
    }
}
