<?php

namespace wcf\action;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Laminas\Diactoros\Response\RedirectResponse;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\ConstantTime\Hex;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use wcf\data\user\User;
use wcf\form\AccountManagementForm;
use wcf\form\RegisterForm;
use wcf\system\event\EventHandler;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\io\HttpFactory;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\event\UserLoggedIn;
use wcf\system\user\authentication\oauth\exception\StateValidationException;
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
final class TwitterAuthAction extends AbstractAction
{
    /**
     * @inheritDoc
     */
    public $neededModules = ['TWITTER_PUBLIC_KEY', 'TWITTER_PRIVATE_KEY'];

    private ClientInterface $httpClient;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (WCF::getSession()->spiderID) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function execute(): ResponseInterface
    {
        parent::execute();

        try {
            if (isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])) {
                $token = $this->verifierToAccessToken(
                    $_GET['oauth_token'],
                    $_GET['oauth_verifier']
                );

                $oauthUser = $this->getUser($token);

                return $this->processUser($oauthUser);
            } elseif (isset($_GET['denied'])) {
                throw new NamedUserException(
                    WCF::getLanguage()->getDynamicVariable('wcf.user.3rdparty.login.error.denied')
                );
            } else {
                return $this->initiate();
            }
        } catch (NamedUserException | PermissionDeniedException $e) {
            throw $e;
        } catch (StateValidationException $e) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable(
                'wcf.user.3rdparty.login.error.stateValidation'
            ));
        } catch (\Exception $e) {
            $exceptionID = \wcf\functions\exception\logThrowable($e);

            $type = 'genericException';
            if ($e instanceof ClientExceptionInterface) {
                $type = 'httpError';
            }

            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable(
                'wcf.user.3rdparty.login.error.' . $type,
                [
                    'exceptionID' => $exceptionID,
                ]
            ));
        }

        throw new \LogicException("Unreachable");
    }

    /**
     * Processes the user (e.g. by registering session variables and redirecting somewhere).
     */
    protected function processUser(OauthUser $oauthUser): ResponseInterface
    {
        $user = User::getUserByAuthData('twitter:' . $oauthUser->getId());

        if ($user->userID) {
            if (WCF::getUser()->userID) {
                // This account belongs to an existing user, but we are already logged in.
                // This can't be handled.

                throw new NamedUserException(
                    WCF::getLanguage()->getDynamicVariable('wcf.user.3rdparty.twitter.connect.error.inuse')
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
            WCF::getSession()->register('__3rdPartyProvider', 'twitter');

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

    /**
     * Turns the access token response into an oauth user.
     */
    private function getUser(array $accessToken): OauthUser
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

    /**
     * Turns the verifier provided by Twitter into an access token.
     */
    private function verifierToAccessToken(string $oauthToken, string $oauthVerifier)
    {
        $initData = WCF::getSession()->getVar('__twitterInit');
        WCF::getSession()->unregister('__twitterInit');
        if (!$initData) {
            throw new StateValidationException('Missing state in session');
        }

        if (!\hash_equals((string)$initData['oauth_token'], $oauthToken)) {
            throw new StateValidationException('oauth_token mismatch');
        }

        $uri = 'https://api.twitter.com/oauth/access_token';
        $oauthHeader = [
            'oauth_consumer_key' => StringUtil::trim(TWITTER_PUBLIC_KEY),
            'oauth_nonce' => Hex::encode(\random_bytes(20)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => TIME_NOW,
            'oauth_version' => '1.0',
            'oauth_token' => $oauthToken,
        ];
        $postData = [
            'oauth_verifier' => $oauthVerifier,
        ];

        $signature = $this->createSignature(
            $uri,
            \array_merge($oauthHeader, $postData)
        );
        $oauthHeader['oauth_signature'] = $signature;

        $request = new Request(
            'POST',
            $uri,
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
    private function getRequestToken()
    {
        $callbackURL = LinkHandler::getInstance()->getControllerLink(static::class);
        $uri = 'https://api.twitter.com/oauth/request_token';
        $oauthHeader = [
            'oauth_callback' => $callbackURL,
            'oauth_consumer_key' => StringUtil::trim(TWITTER_PUBLIC_KEY),
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

    /**
     * Initiates the OAuth flow by redirecting to the '/authenticate' URL.
     */
    private function initiate(): ResponseInterface
    {
        $data = $this->getRequestToken();

        WCF::getSession()->register('__twitterInit', $data);

        return new RedirectResponse(
            \sprintf(
                'https://api.twitter.com/oauth/authenticate?%s',
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
        $key = \rawurlencode(StringUtil::trim(TWITTER_PRIVATE_KEY)) . '&' . \rawurlencode($tokenSecret);

        return Base64::encode(\hash_hmac('sha1', $base, $key, true));
    }

    /**
     * Returns a "static" instance of the HTTP client to use to allow
     * for TCP connection reuse.
     */
    protected function getHttpClient(): ClientInterface
    {
        if (!isset($this->httpClient)) {
            $this->httpClient = HttpFactory::makeClientWithTimeout(5);
        }

        return $this->httpClient;
    }
}
