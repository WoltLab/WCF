<?php

namespace wcf\action;

use CuyZ\Valinor\MapperBuilder;
use GuzzleHttp\Psr7\Request;
use Laminas\Diactoros\Response\RedirectResponse;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\ConstantTime\Hex;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\oauth\exception\StateValidationException;
use wcf\system\user\authentication\oauth\Failure as OAuth2Failure;
use wcf\system\user\authentication\oauth\Success as OAuth2Success;
use wcf\system\user\authentication\oauth\twitter\Failure as OAuth2TwitterFailure;
use wcf\system\user\authentication\oauth\twitter\Success as OAuth2TwitterSuccess;
use wcf\system\user\authentication\oauth\User as OauthUser;
use wcf\system\WCF;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Handles twitter auth.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class TwitterAuthAction extends AbstractOauth2AuthAction
{
    #[\Override]
    protected function isEnabled(): bool
    {
        return !empty(TWITTER_PUBLIC_KEY) && !empty(TWITTER_PRIVATE_KEY);
    }

    #[\Override]
    protected function getClientId(): string
    {
        return StringUtil::trim(TWITTER_PUBLIC_KEY);
    }

    #[\Override]
    protected function getClientSecret(): string
    {
        return StringUtil::trim(TWITTER_PRIVATE_KEY);
    }

    #[\Override]
    protected function getCallbackUrl(): string
    {
        return LinkHandler::getInstance()->getControllerLink(self::class);
    }

    #[\Override]
    protected function getTokenEndpoint(): string
    {
        return 'https://api.twitter.com/oauth/access_token';
    }

    #[\Override]
    protected function supportsState(): bool
    {
        return false;
    }

    #[\Override]
    protected function getProviderName(): string
    {
        return 'twitter';
    }

    #[\Override]
    protected function getScope(): string
    {
        // Twitter OAuth 1.0a does not support scopes
        return '';
    }

    #[\Override]
    protected function getAuthorizeUrl(): string
    {
        return 'https://api.twitter.com/oauth/authenticate';
    }

    #[\Override]
    protected function mapParameters(ServerRequestInterface $request): OAuth2Success | OAuth2Failure | null
    {
        try {
            $mapper = (new MapperBuilder())
                ->allowSuperfluousKeys()
                ->enableFlexibleCasting()
                ->mapper();

            return $mapper->map(
                \sprintf("%s|%s", OAuth2TwitterSuccess::class, OAuth2TwitterFailure::class),
                $request->getQueryParams()
            );
        } catch (\Throwable) {
            return null;
        }
    }

    #[\Override]
    protected function getUser(array $accessToken): OauthUser
    {
        $uri = 'https://api.twitter.com/1.1/account/verify_credentials.json';
        $oauthHeader = [
            'oauth_consumer_key' => StringUtil::trim(TWITTER_PUBLIC_KEY),
            'oauth_nonce' => Hex::encode(\random_bytes(20)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => TIME_NOW,
            'oauth_version' => '1.0',
            'oauth_token' => $accessToken['oauth_token'],
        ];
        $queryParameters = [
            'include_email' => 'true',
            'skip_status' => 'true',
        ];
        $signature = $this->createSignature(
            $uri,
            \array_merge($oauthHeader, $queryParameters),
            $accessToken['oauth_token_secret'],
            'GET'
        );
        $oauthHeader['oauth_signature'] = $signature;

        $request = new Request(
            'GET',
            \sprintf(
                '%s?%s',
                $uri,
                \http_build_query($queryParameters, '', '&')
            ),
            [
                'authorization' => \sprintf('OAuth %s', $this->buildOAuthHeader($oauthHeader)),
            ]
        );
        $response = $this->getHttpClient()->send($request);

        $parsed = JSON::decode((string)$response->getBody());
        $parsed['__id'] = $parsed['id'];
        $parsed['__username'] = $parsed['name'];
        if (!empty($parsed['email'])) {
            $parsed['__email'] = $parsed['email'];
        }

        return new OauthUser($parsed);
    }

    #[\Override]
    protected function getAccessToken(OAuth2Success $auth2Success): array
    {
        $initData = WCF::getSession()->getVar('__twitterInit');
        WCF::getSession()->unregister('__twitterInit');
        if (!$initData) {
            throw new StateValidationException('Missing state in session');
        }

        if (!\hash_equals((string)$initData['oauth_token'], $auth2Success->code)) {
            throw new StateValidationException('oauth_token mismatch');
        }

        $oauthHeader = [
            'oauth_consumer_key' => $this->getClientId(),
            'oauth_nonce' => Hex::encode(\random_bytes(20)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => TIME_NOW,
            'oauth_version' => '1.0',
            'oauth_token' => $auth2Success->code,
        ];
        $postData = [
            'oauth_verifier' => $auth2Success->state,
        ];

        $signature = $this->createSignature(
            $this->getTokenEndpoint(),
            \array_merge($oauthHeader, $postData)
        );
        $oauthHeader['oauth_signature'] = $signature;

        $request = new Request(
            'POST',
            $this->getTokenEndpoint(),
            [
                'authorization' => \sprintf('OAuth %s', $this->buildOAuthHeader($oauthHeader)),
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            \http_build_query($postData, '', '&', \PHP_QUERY_RFC1738)
        );
        $response = $this->getHttpClient()->send($request);

        \parse_str((string)$response->getBody(), $data);

        if (!isset($data['oauth_token'])) {
            throw new \Exception("Access token response does not have the 'oauth_token' key.");
        }

        if (!isset($data['oauth_token_secret'])) {
            throw new \Exception("Access token response does not have the 'oauth_token_secret' key.");
        }

        return $data;
    }

    /**
     * Requests an request_token to initiate the OAuth flow.
     */
    private function getRequestToken(): array
    {
        $uri = 'https://api.twitter.com/oauth/request_token';
        $oauthHeader = [
            'oauth_callback' => $this->getCallbackUrl(),
            'oauth_consumer_key' => $this->getClientId(),
            'oauth_nonce' => Hex::encode(\random_bytes(20)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => TIME_NOW,
            'oauth_version' => '1.0',
        ];
        $signature = $this->createSignature($uri, $oauthHeader);
        $oauthHeader['oauth_signature'] = $signature;

        // call api
        $request = new Request(
            'POST',
            $uri,
            [
                'authorization' => \sprintf('OAuth %s', $this->buildOAuthHeader($oauthHeader)),
            ]
        );
        $response = $this->getHttpClient()->send($request);

        \parse_str((string)$response->getBody(), $data);

        if (
            !isset($data['oauth_callback_confirmed'])
            || $data['oauth_callback_confirmed'] !== 'true'
        ) {
            throw new \Exception("Request token response does not have the 'oauth_callback_confirmed' key set to 'true'.");
        }

        if (!isset($data['oauth_token'])) {
            throw new \Exception("Request token response does not have the 'oauth_token' key.");
        }

        return $data;
    }

    #[\Override]
    protected function initiate(): ResponseInterface
    {
        $data = $this->getRequestToken();

        WCF::getSession()->register('__twitterInit', $data);

        return new RedirectResponse(
            \sprintf(
                '%s?%s',
                $this->getAuthorizeUrl(),
                \http_build_query([
                    'oauth_token' => $data['oauth_token'],
                ], '', '&')
            )
        );
    }

    /**
     * Builds the OAuth authorization header.
     *
     * @param array $parameters
     */
    public function buildOAuthHeader(array $parameters): string
    {
        $header = '';
        foreach ($parameters as $key => $val) {
            if ($header !== '') {
                $header .= ', ';
            }
            $header .= \sprintf(
                '%s="%s"',
                \rawurlencode($key),
                \rawurlencode($val)
            );
        }

        return $header;
    }

    /**
     * Creates an OAuth 1 signature.
     */
    public function createSignature(
        string $url,
        array $parameters,
        #[\SensitiveParameter]
        string $tokenSecret = '',
        string $method = 'POST'
    ): string {
        $tmp = [];
        foreach ($parameters as $key => $val) {
            $tmp[\rawurlencode($key)] = \rawurlencode($val);
        }
        $parameters = $tmp;

        \uksort($parameters, 'strcmp');
        $parameterString = '';
        foreach ($parameters as $key => $val) {
            if ($parameterString !== '') {
                $parameterString .= '&';
            }
            $parameterString .= $key . '=' . $val;
        }

        $base = $method . "&" . \rawurlencode($url) . "&" . \rawurlencode($parameterString);
        $key = \rawurlencode($this->getClientSecret()) . '&' . \rawurlencode($tokenSecret);

        return Base64::encode(\hash_hmac('sha1', $base, $key, true));
    }
}
