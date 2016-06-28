<?php
namespace wcf\action;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\HTTPRequest;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Handles google auth.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 */
class GoogleAuthAction extends AbstractAction {
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['GOOGLE_PUBLIC_KEY', 'GOOGLE_PRIVATE_KEY'];
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		$callbackURL = LinkHandler::getInstance()->getLink('GoogleAuth', [
			'appendSession' => false
		]);
		// user accepted the connection
		if (isset($_GET['code'])) {
			try {
				// fetch access_token
				$request = new HTTPRequest('https://accounts.google.com/o/oauth2/token', [], [
					'code' => $_GET['code'],
					'client_id' => StringUtil::trim(GOOGLE_PUBLIC_KEY),
					'client_secret' => StringUtil::trim(GOOGLE_PRIVATE_KEY),
					'redirect_uri' => $callbackURL,
					'grant_type' => 'authorization_code'
				]);
				$request->execute();
				$reply = $request->getReply();
				
				$content = $reply['body'];
			}
			catch (SystemException $e) {
				\wcf\functions\exception\logThrowable($e);
				throw new IllegalLinkException();
			}
			
			// validate state, validation of state is executed after fetching the access_token to invalidate 'code'
			if (!isset($_GET['state']) || $_GET['state'] != WCF::getSession()->getVar('__googleInit')) throw new IllegalLinkException();
			WCF::getSession()->unregister('__googleInit');
			
			$data = JSON::decode($content);
			
			try {
				// fetch userdata
				$request = new HTTPRequest('https://www.googleapis.com/plus/v1/people/me');
				$request->addHeader('Authorization', 'Bearer '.$data['access_token']);
				$request->execute();
				$reply = $request->getReply();
				
				$content = $reply['body'];
			}
			catch (SystemException $e) {
				\wcf\functions\exception\logThrowable($e);
				throw new IllegalLinkException();
			}
			
			$userData = JSON::decode($content);
			
			// check whether a user is connected to this google account
			$user = User::getUserByAuthData('google:'.$userData['id']);
			
			if ($user->userID) {
				// a user is already connected, but we are logged in, break
				if (WCF::getUser()->userID) {
					throw new NamedUserException(WCF::getLanguage()->get('wcf.user.3rdparty.google.connect.error.inuse'));
				}
				// perform login
				else {
					if (UserAuthenticationFactory::getInstance()->getUserAuthentication()->supportsPersistentLogins()) {
						$password = StringUtil::getRandomID();
						$userEditor = new UserEditor($user);
						$userEditor->update(['password' => $password]);
						
						// reload user to retrieve salt
						$user = new User($user->userID);
						
						UserAuthenticationFactory::getInstance()->getUserAuthentication()->storeAccessData($user, $user->username, $password);
					}
					
					WCF::getSession()->changeUser($user);
					WCF::getSession()->update();
					HeaderUtil::redirect(LinkHandler::getInstance()->getLink());
				}
			}
			else {
				WCF::getSession()->register('__3rdPartyProvider', 'google');
				
				// save data for connection
				if (WCF::getUser()->userID) {
					WCF::getSession()->register('__googleUsername', $userData['displayName']);
					WCF::getSession()->register('__googleData', $userData);
					
					HeaderUtil::redirect(LinkHandler::getInstance()->getLink('AccountManagement').'#3rdParty');
				}
				// save data and redirect to registration
				else {
					WCF::getSession()->register('__username', $userData['displayName']);
					if (isset($userData['emails'][0]['value'])) {
						WCF::getSession()->register('__email', $userData['emails'][0]['value']);
					}
					
					WCF::getSession()->register('__googleData', $userData);
					
					// we assume that bots won't register on google first
					// thus no need for a captcha
					if (REGISTER_USE_CAPTCHA) {
						WCF::getSession()->register('noRegistrationCaptcha', true);
					}
					
					WCF::getSession()->update();
					HeaderUtil::redirect(LinkHandler::getInstance()->getLink('Register'));
				}
			}
			
			$this->executed();
			exit;
		}
		// user declined or any other error that may occur
		if (isset($_GET['error'])) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.3rdparty.google.login.error.'.$_GET['error']));
		}
		
		// start auth by redirecting to google
		$token = StringUtil::getRandomID();
		WCF::getSession()->register('__googleInit', $token);
		HeaderUtil::redirect("https://accounts.google.com/o/oauth2/auth?client_id=".rawurlencode(StringUtil::trim(GOOGLE_PUBLIC_KEY)). "&redirect_uri=".rawurlencode($callbackURL)."&state=".$token."&scope=profile+email&response_type=code");
		$this->executed();
		exit;
	}
}
