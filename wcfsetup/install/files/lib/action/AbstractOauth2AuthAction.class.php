<?php

namespace wcf\action;

use CuyZ\Valinor\MapperBuilder;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Uri;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\ConstantTime\Hex;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\User;
use wcf\form\AccountManagementForm;
use wcf\form\RegisterForm;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\io\HttpFactory;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\event\UserLoggedIn;
use wcf\system\user\authentication\LoginRedirect;
use wcf\system\user\authentication\oauth\exception\StateValidationException;
use wcf\system\user\authentication\oauth\Failure as OAuth2Failure;
use wcf\system\user\authentication\oauth\Success as OAuth2Success;
use wcf\system\user\authentication\oauth\User as OauthUser;
use wcf\system\WCF;
use wcf\util\JSON;

/**
 * Generic implementation to handle the OAuth 2 flow.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
abstract class AbstractOauth2AuthAction implements RequestHandlerInterface
{
    private const STATE = self::class . "\0state_parameter";

    private const PKCE = self::class . "\0pkce";

    private ClientInterface $httpClient;

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isEnabled()) {
            throw new IllegalLinkException();
        }
        if (WCF::getSession()->spiderIdentifier) {
            throw new PermissionDeniedException();
        }

        $parameters = $this->mapParameters($request);

        try {
            if ($parameters instanceof OAuth2Success) {
                $accessToken = $this->getAccessToken($parameters);
                $user = $this->getUser($accessToken);

                return $this->processUser($user);
            } elseif ($parameters instanceof OAuth2Failure) {
                return $this->handleError($parameters);
            } else {
                return $this->initiate();
            }
        } catch (NamedUserException $e) {
            throw $e;
        } catch (StateValidationException $e) {
            throw new NamedUserException(
                WCF::getLanguage()->getDynamicVariable(
                    'wcf.user.3rdparty.login.error.stateValidation'
                )
            );
        } catch (\Exception $e) {
            $exceptionID = \wcf\functions\exception\logThrowable($e);

            $type = 'genericException';
            if ($e instanceof ClientExceptionInterface) {
                $type = 'httpError';
            }

            throw new NamedUserException(
                WCF::getLanguage()->getDynamicVariable(
                    'wcf.user.3rdparty.login.error.' . $type,
                    [
                        'exceptionID' => $exceptionID,
                    ]
                )
            );
        }
    }

    /**
     * Returns whether this OAuth provider is enabled.
     */
    abstract protected function isEnabled(): bool;

    protected function mapParameters(ServerRequestInterface $request): OAuth2Success | OAuth2Failure | null
    {
        try {
            $mapper = (new MapperBuilder())
                ->allowSuperfluousKeys()
                ->enableFlexibleCasting()
                ->mapper();

            return $mapper->map(
                \sprintf("%s|%s", OAuth2Success::class, OAuth2Failure::class),
                $request->getQueryParams()
            );
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Turns the 'code' into an access token.
     */
    protected function getAccessToken(OAuth2Success $auth2Success): array
    {
        $payload = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'redirect_uri' => $this->getCallbackUrl(),
            'code' => $auth2Success->code,
        ];

        if ($this->usePkce()) {
            if (!($verifier = WCF::getSession()->getVar(self::PKCE))) {
                throw new StateValidationException('Missing PKCE verifier in session');
            }

            $payload['code_verifier'] = $verifier;
        }

        $request = new Request('POST', $this->getTokenEndpoint(), [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], \http_build_query($payload, '', '&', \PHP_QUERY_RFC1738));

        try {
            $response = $this->getHttpClient()->send($request);
        } finally {
            // Validate state. Validation of state is executed after fetching the
            // access_token to invalidate 'code'.
            //
            // Validation is happening within the `finally` so that the StateValidationException
            // overwrites any HTTP exception (improving the error message).
            if ($this->supportsState()) {
                $this->validateState($auth2Success);
            }
        }

        $parsed = JSON::decode((string)$response->getBody());

        if (!empty($parsed['error'])) {
            throw new \Exception(
                \sprintf(
                    "Access token response indicates an error: '%s'",
                    $parsed['error']
                )
            );
        }

        if (empty($parsed['access_token'])) {
            throw new \Exception("Access token response does not have the 'access_token' key.");
        }

        return $parsed;
    }

    /**
     * Returns the 'client_id'.
     */
    abstract protected function getClientId(): string;

    /**
     * Returns the 'client_secret'.
     */
    abstract protected function getClientSecret(): string;

    /**
     * Returns the callback URL. This should most likely be:
     *
     * LinkHandler::getInstance()->getControllerLink(self::class)
     */
    abstract protected function getCallbackUrl(): string;

    /**
     * Whether to use PKCE (RFC 7636). Defaults to 'false'.
     */
    protected function usePkce(): bool
    {
        return false;
    }

    /**
     * Returns the URL of the '/token' endpoint that turns the code into an access token.
     */
    abstract protected function getTokenEndpoint(): string;

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

    /**
     * Whether to validate the state or not. Should be 'true' to protect
     * against CSRF attacks.
     */
    abstract protected function supportsState(): bool;

    /**
     * Validates the state parameter.
     */
    protected function validateState(OAuth2Success $auth2Success): void
    {
        try {
            if (!($sessionState = WCF::getSession()->getVar(self::STATE))) {
                throw new StateValidationException('Missing state in session');
            }
            if (!\hash_equals($sessionState, $auth2Success->state)) {
                throw new StateValidationException('Mismatching state');
            }
        } finally {
            WCF::getSession()->unregister(self::STATE);
        }
    }

    /**
     * Turns the access token response into an oauth user.
     */
    abstract protected function getUser(array $accessToken): OauthUser;

    /**
     * Processes the user (e.g. by registering session variables and redirecting somewhere).
     */
    protected function processUser(OauthUser $oauthUser): ResponseInterface
    {
        $user = $this->getInternalUser($oauthUser);

        if ($user->userID) {
            if (WCF::getUser()->userID) {
                // This account belongs to an existing user, but we are already logged in.
                // This can't be handled.

                throw new NamedUserException($this->getInUseErrorMessage());
            } else {
                // This account belongs to an existing user, we are not logged in.
                // Perform the login.

                WCF::getSession()->changeUser($user);
                WCF::getSession()->update();
                EventHandler::getInstance()->fire(
                    new UserLoggedIn($user)
                );

                return new RedirectResponse(
                    LoginRedirect::getUrl()
                );
            }
        } else {
            WCF::getSession()->register('__3rdPartyProvider', $this->getProviderName());

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
                return $this->performeRegister($oauthUser);
            }
        }
    }

    /**
     * Returns the user who is assigned to the OAuth user.
     */
    protected function getInternalUser(OauthUser $oauthUser): User
    {
        return User::getUserByAuthData(\sprintf("%s:%s", $this->getProviderName(), $oauthUser->getId()));
    }

    /**
     * Returns the name of the provider.
     */
    abstract protected function getProviderName(): string;

    /**
     * Returns the error message if the user is logged in and the external account is linked to another user.
     */
    protected function getInUseErrorMessage(): string
    {
        return WCF::getLanguage()->getDynamicVariable(
            "wcf.user.3rdparty.{$this->getProviderName()}.connect.error.inuse"
        );
    }

    protected function performeRegister(OauthUser $oauthUser): ResponseInterface
    {
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

    protected function handleError(OAuth2Failure $oauth2Failure): ResponseInterface
    {
        throw new NamedUserException(
            WCF::getLanguage()->getDynamicVariable('wcf.user.3rdparty.login.error.' . $oauth2Failure->error)
        );
    }

    /**
     * Initiates the OAuth flow by redirecting to the '/authorize' URL.
     */
    protected function initiate(): ResponseInterface
    {
        $parameters = [
            'response_type' => 'code',
            'client_id' => $this->getClientId(),
            'scope' => $this->getScope(),
            'redirect_uri' => $this->getCallbackUrl(),
        ];

        if ($this->supportsState()) {
            $token = Hex::encode(\random_bytes(16));
            WCF::getSession()->register(self::STATE, $token);

            $parameters['state'] = $token;
        }

        if ($this->usePkce()) {
            $verifier = Hex::encode(\random_bytes(32));
            WCF::getSession()->register(self::PKCE, $verifier);

            $parameters['code_challenge'] = Base64UrlSafe::encodeUnpadded(\hash('sha256', $verifier, true));
            $parameters['code_challenge_method'] = 'S256';
        }

        $encodedParameters = \http_build_query($parameters, '', '&');

        $url = new Uri($this->getAuthorizeUrl());
        $query = $url->getQuery();
        if ($query !== '') {
            $url = $url->withQuery("{$query}&{$encodedParameters}");
        } else {
            $url = $url->withQuery($encodedParameters);
        }

        return new RedirectResponse($url);
    }

    /**
     * Returns the 'scope' to request.
     */
    abstract protected function getScope(): string;

    /**
     * Returns the URL of the '/authorize' endpoint where the user is redirected to.
     */
    abstract protected function getAuthorizeUrl(): string;
}
