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
 * Handles github auth.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 */
class GithubAuthAction extends AbstractAction {
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['GITHUB_PUBLIC_KEY', 'GITHUB_PRIVATE_KEY'];
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		// user accepted the connection
		if (isset($_GET['code'])) {
			try {
				// fetch access_token
				$request = new HTTPRequest('https://github.com/login/oauth/access_token', [], [
					'client_id' => StringUtil::trim(GITHUB_PUBLIC_KEY),
					'client_secret' => StringUtil::trim(GITHUB_PRIVATE_KEY),
					'code' => $_GET['code']
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
			if (!isset($_GET['state']) || $_GET['state'] != WCF::getSession()->getVar('__githubInit')) throw new IllegalLinkException();
			WCF::getSession()->unregister('__githubInit');
			
			parse_str($content, $data);
			
			// check whether the token is okay
			if (isset($data['error'])) throw new IllegalLinkException();
			
			try {
				// fetch userdata
				$request = new HTTPRequest('https://api.github.com/user?access_token='.$data['access_token']);
				$request->execute();
				$reply = $request->getReply();
				$userData = JSON::decode(StringUtil::trim($reply['body']));
			}
			catch (SystemException $e) {
				\wcf\functions\exception\logThrowable($e);
				throw new IllegalLinkException();
			}
			
			// check whether a user is connected to this github account
			$user = User::getUserByAuthData('github:'.$userData['id']);
			if (!$user->userID) {
				$user = User::getUserByAuthData('github:'.$data['access_token']);
				$userEditor = new UserEditor($user);
				$userEditor->update(['authData' => 'github:'.$userData['id']]);
			}
			
			if ($user->userID) {
				// a user is already connected, but we are logged in, break
				if (WCF::getUser()->userID) {
					throw new NamedUserException(WCF::getLanguage()->get('wcf.user.3rdparty.github.connect.error.inuse'));
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
				WCF::getSession()->register('__3rdPartyProvider', 'github');
				// save data for connection
				if (WCF::getUser()->userID) {
					WCF::getSession()->register('__githubUsername', $userData['login']);
					WCF::getSession()->register('__githubToken', $data['access_token']);
					
					HeaderUtil::redirect(LinkHandler::getInstance()->getLink('AccountManagement').'#3rdParty');
				}
				// save data and redirect to registration
				else {
					WCF::getSession()->register('__githubData', $userData);
					WCF::getSession()->register('__username', $userData['login']);
					
					try {
						$request = new HTTPRequest('https://api.github.com/user/emails?access_token='.$data['access_token']);
						$request->execute();
						$reply = $request->getReply();
						$emails = JSON::decode(StringUtil::trim($reply['body']));
						
						// search primary email
						$email = $emails[0]['email'];
						foreach ($emails as $tmp) {
							if ($tmp['primary']) $email = $tmp['email'];
							break;
						}
						WCF::getSession()->register('__email', $email);
					}
					catch (SystemException $e) {
					}
					WCF::getSession()->register('__githubToken', $data['access_token']);
					
					// we assume that bots won't register on github first
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
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.3rdparty.github.login.error.'.$_GET['error']));
		}
		
		// start auth by redirecting to github
		$token = StringUtil::getRandomID();
		WCF::getSession()->register('__githubInit', $token);
		HeaderUtil::redirect("https://github.com/login/oauth/authorize?client_id=".rawurlencode(StringUtil::trim(GITHUB_PUBLIC_KEY))."&scope=".rawurlencode('user:email')."&state=".$token);
		$this->executed();
		exit;
	}
}
