<?php

namespace wcf\action;

use GuzzleHttp\Psr7\Request;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\oauth\User as OauthUser;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Performs authentication against Facebook
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class FacebookAuthAction extends AbstractOauth2AuthAction
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

    #[\Override]
    protected function getTokenEndpoint(): string
    {
        return 'https://graph.facebook.com/oauth/access_token';
    }

    #[\Override]
    protected function getClientId(): string
    {
        return StringUtil::trim(FACEBOOK_PUBLIC_KEY);
    }

    #[\Override]
    protected function getClientSecret(): string
    {
        return StringUtil::trim(FACEBOOK_PRIVATE_KEY);
    }

    #[\Override]
    protected function getScope(): string
    {
        return 'email';
    }

    #[\Override]
    protected function getAuthorizeUrl(): string
    {
        return 'https://www.facebook.com/dialog/oauth';
    }

    #[\Override]
    protected function getCallbackUrl(): string
    {
        $callbackURL = LinkHandler::getInstance()->getControllerLink(self::class);

        // Work around Facebook performing an illegal substitution of the Slash
        // by '%2F' when entering redirect URI (RFC 3986 sect. 2.2, sect. 3.4)
        return \preg_replace_callback('/(?<=\?).*/', static function ($matches) {
            return \rawurlencode($matches[0]);
        }, $callbackURL);
    }

    #[\Override]
    protected function supportsState(): bool
    {
        return true;
    }

    #[\Override]
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

    #[\Override]
    protected function getProviderName(): string
    {
        return 'facebook';
    }
}
