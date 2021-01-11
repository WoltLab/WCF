<?php
namespace wcf\action;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use ParagonIE\ConstantTime\Hex;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\io\HttpFactory;
use wcf\system\user\authentication\oauth\exception\StateValidationException;
use wcf\system\user\authentication\oauth\User as OauthUser;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\JSON;

/**
 * Generic implementation to handle the OAuth 2 flow.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2021 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 */
abstract class AbstractOauth2Action extends AbstractAction {
	private const STATE = self::class . "\0state_parameter";
	
	/**
	 * @var ClientInterface
	 */
	private $httpClient;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (WCF::getSession()->spiderID) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Returns a "static" instance of the HTTP client to use to allow
	 * for TCP connection reuse.
	 */
	protected final function getHttpClient(): ClientInterface {
		if (!$this->httpClient) {
			$this->httpClient = HttpFactory::makeClient([
				'timeout' => 5,
			]);
		}
		
		return $this->httpClient;
	}
	
	/**
	 * Returns the URL of the '/token' endpoint that turns the code into an access token.
	 */
	abstract protected function getTokenEndpoint(): string;
	
	/**
	 * Returns the 'client_id'.
	 */
	abstract protected function getClientId(): string;
	
	/**
	 * Returns the 'client_secret'.
	 */
	abstract protected function getClientSecret(): string;
	
	/**
	 * Returns the 'scope' to request.
	 */
	abstract protected function getScope(): string;
	
	/**
	 * Returns the URL of the '/authorize' endpoint where the user is redirected to.
	 */
	abstract protected function getAuthorizeUrl(): string;
	
	/**
	 * Returns the callback URL. This should most likely be:
	 * 
	 * LinkHandler::getInstance()->getControllerLink(self::class)
	 */
	abstract protected function getCallbackUrl(): string;
	
	/**
	 * Whether to validate the state or not. Should be 'true' to protect
	 * against CSRF attacks.
	 */
	abstract protected function supportsState(): bool;
	
	/**
	 * Turns the access token response into an oauth user.
	 */
	abstract protected function getUser(array $accessToken): OauthUser;
	
	/**
	 * Processes the user (e.g. by registering session variables and redirecting somewhere).
	 */
	abstract protected function processUser(OauthUser $oauthUser);

	/**
	 * Validates the state parameter.
	 */
	protected function validateState() {
		if (!isset($_GET['state'])) {
			throw new StateValidationException('Missing state parameter');
		}
		if (!($sessionState = WCF::getSession()->getVar(self::STATE))) {
			throw new StateValidationException('Missing state in session');
		}
		if (!\hash_equals($sessionState, (string) $_GET['state'])) {
			throw new StateValidationException('Mismatching state');
		}
		
		WCF::getSession()->unregister(self::STATE);
	}
	
	/**
	 * Turns the 'code' into an access token.
	 */
	protected function codeToAccessToken(string $code): array {
		$request = new Request('POST', $this->getTokenEndpoint(), [
			'Accept' => 'application/json',
			'Content-Type' => 'application/x-www-form-urlencoded',
		], http_build_query([
			'grant_type' => 'authorization_code',
			'client_id' => $this->getClientId(),
			'client_secret' => $this->getClientSecret(),
			'redirect_uri' => $this->getCallbackUrl(),
			'code' => $_GET['code'],
		], '', '&', PHP_QUERY_RFC1738));
		
		try {
			$response = $this->getHttpClient()->send($request);
		}
		finally {
			// Validate state. Validation of state is executed after fetching the
			// access_token to invalidate 'code'.
			//
			// Validation is happening within the `finally` so that the StateValidationException
			// overwrites any GuzzleException (improving the error message).
			if ($this->supportsState()) {
				$this->validateState();
			}
		}
		
		$parsed = JSON::decode((string) $response->getBody());
		
		if (!empty($parsed['error'])) {
			throw new \Exception(\sprintf(
				"Access token response indicates an error: '%s'",
				$parsed['error']
			));
		}
		
		if (empty($parsed['access_token'])) {
			throw new \Exception("Access token response does not have the 'access_token' key.");
		}
		
		return $parsed;
	}
	
	protected function handleError(string $error) {
		throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.3rdparty.login.error.'.$error));
	}
	
	/**
	 * Initiates the OAuth flow by redirecting to the '/authorize' URL.
	 */
	protected function initiate() {
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
		
		$url = $this->getAuthorizeUrl().'?'.http_build_query($parameters, '', '&');
		
		HeaderUtil::redirect($url);
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		try {
			if (isset($_GET['code'])) {
				$accessToken = $this->codeToAccessToken($_GET['code']);
				$oauthUser = $this->getUser($accessToken);
				
				$this->processUser($oauthUser);
			}
			else if (isset($_GET['error'])) {
				$this->handleError($_GET['error']);
			}
			else {
				$this->initiate();
			}
		}
		catch (NamedUserException | PermissionDeniedException $e) {
			throw $e;
		}
		catch (StateValidationException $e) {
			throw new NamedUserException(WCF::getLanguage()->getDynamicVariable(
				'wcf.user.3rdparty.login.error.stateValidation'
			));
		}
		catch (\Exception $e) {
			$exceptionID = \wcf\functions\exception\logThrowable($e);
			
			$type = 'genericException';
			if ($e instanceof GuzzleException) {
				$type = 'httpError';
			}
			
			throw new NamedUserException(WCF::getLanguage()->getDynamicVariable(
				'wcf.user.3rdparty.login.error.'.$type,
				[
					'exceptionID' => $exceptionID,
				]
			));
		}
	}
}
