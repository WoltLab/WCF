<?php

namespace wcf\action;

use GuzzleHttp\Psr7\Request;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\oauth\User as OauthUser;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Performs authentication against Google (GAIA).
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class GoogleAuthAction extends AbstractOauth2AuthAction
{
    private array $configuration;

    /**
     * Returns Google's OpenID Connect configuration.
     */
    private function getConfiguration(): array
    {
        if (!isset($this->configuration)) {
            $request = new Request('GET', 'https://accounts.google.com/.well-known/openid-configuration');
            $response = $this->getHttpClient()->send($request);

            $this->configuration = JSON::decode((string)$response->getBody());
        }

        return $this->configuration;
    }

    #[\Override]
    protected function getTokenEndpoint(): string
    {
        return $this->getConfiguration()['token_endpoint'];
    }

    #[\Override]
    protected function getClientId(): string
    {
        return StringUtil::trim(GOOGLE_PUBLIC_KEY);
    }

    #[\Override]
    protected function getClientSecret(): string
    {
        return StringUtil::trim(GOOGLE_PRIVATE_KEY);
    }

    #[\Override]
    protected function getScope(): string
    {
        return 'profile openid email';
    }

    #[\Override]
    protected function getAuthorizeUrl(): string
    {
        return $this->getConfiguration()['authorization_endpoint'];
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

    #[\Override]
    protected function getProviderName(): string
    {
        return 'google';
    }
}
