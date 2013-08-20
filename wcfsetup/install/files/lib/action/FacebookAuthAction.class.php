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
 * Handles facebook auth.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category	Community Framework
 */
class FacebookAuthAction extends AbstractAction {
	/**
	 * @see	wcf\action\AbstractAction::$neededModules
	 */
	public $neededModules = array('FACEBOOK_PUBLIC_KEY', 'FACEBOOK_PRIVATE_KEY');
	
	/**
	 * @see	wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		$callbackURL = LinkHandler::getInstance()->getLink('FacebookAuth');
		// user accepted the connection
		if (isset($_GET['code'])) {
			try {
				// fetch access_token
				$request = new HTTPRequest('https://graph.facebook.com/oauth/access_token?client_id='.StringUtil::trim(FACEBOOK_PUBLIC_KEY).'&redirect_uri='.rawurlencode($callbackURL).'&client_secret='.StringUtil::trim(FACEBOOK_PRIVATE_KEY).'&code='.rawurlencode($_GET['code']));
				$request->execute();
				$reply = $request->getReply();
				
				$content = $reply['body'];
			}
			catch (SystemException $e) {
				throw new IllegalLinkException();
			}
			
			// validate state, validation of state is executed after fetching the access_token to invalidate 'code'
			if (!isset($_GET['state']) || $_GET['state'] != WCF::getSession()->getVar('__facebookInit')) throw new IllegalLinkException();
			WCF::getSession()->unregister('__facebookInit');
			
			parse_str($content, $data);
			
			try {
				// fetch userdata
				$request = new HTTPRequest('https://graph.facebook.com/me?access_token='.rawurlencode($data['access_token']).'&fields=birthday,bio,email,gender,id,location,name,picture.type(large),website');
				$request->execute();
				$reply = $request->getReply();
				
				$content = $reply['body'];
			}
			catch (SystemException $e) {
				throw new IllegalLinkException();
			}
			
			$userData = JSON::decode($content);
			
			// check whether a user is connected to this facebook account
			$user = $this->getUser($userData['id']);
			
			if ($user->userID) {
				// a user is already connected, but we are logged in, break
				if (WCF::getUser()->userID) {
					throw new NamedUserException(WCF::getLanguage()->get('wcf.user.3rdparty.facebook.connect.error.inuse'));
				}
				// perform login
				else {
					if (UserAuthenticationFactory::getInstance()->getUserAuthentication()->supportsPersistentLogins()) {
						$password = StringUtil::getRandomID();
						$userEditor = new UserEditor($user);
						$userEditor->update(array('password' => $password));
						
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
				WCF::getSession()->register('__3rdPartyProvider', 'facebook');
				// save data for connection
				if (WCF::getUser()->userID) {
					WCF::getSession()->register('__facebookUsername', $userData['name']);
					WCF::getSession()->register('__facebookData', $userData);
					
					HeaderUtil::redirect(LinkHandler::getInstance()->getLink('AccountManagement').'#3rdParty');
				}
				// save data and redirect to registration
				else {
					WCF::getSession()->register('__username', $userData['name']);
					WCF::getSession()->register('__email', $userData['email']);
					WCF::getSession()->register('__facebookData', $userData);
					
					// we assume that bots won't register on facebook first
					WCF::getSession()->register('recaptchaDone', true);
					
					WCF::getSession()->update();
					HeaderUtil::redirect(LinkHandler::getInstance()->getLink('Register'));
				}
			}
			
			$this->executed();
			exit;
		}
		// user declined or any other error that may occur
		if (isset($_GET['error'])) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.3rdparty.facebook.login.error.'.$_GET['error']));
		}
		
		// start auth by redirecting to facebook
		$token = StringUtil::getRandomID();
		WCF::getSession()->register('__facebookInit', $token);
		HeaderUtil::redirect("https://www.facebook.com/dialog/oauth?client_id=".StringUtil::trim(FACEBOOK_PUBLIC_KEY). "&redirect_uri=".rawurlencode($callbackURL)."&state=".$token."&scope=email,user_about_me,user_birthday,user_interests,user_location,user_website");
		$this->executed();
		exit;
	}
	
	/**
	 * Fetches the User with the given userID.
	 * 
	 * @param	integer			$userID
	 * @return	wcf\data\user\User
	 */
	public function getUser($userID) {
		$sql = "SELECT	userID
			FROM	wcf".WCF_N."_user
			WHERE	authData = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array('facebook:'.$userID));
		$row = $statement->fetchArray();
		
		if ($row === false) {
			$row = array('userID' => 0);
		}
		
		$user = new User($row['userID']);
		return $user;
	}
}
