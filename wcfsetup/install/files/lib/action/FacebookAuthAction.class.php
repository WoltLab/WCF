<?php
namespace wcf\action;
use ParagonIE\ConstantTime\Hex;
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
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 */
class FacebookAuthAction extends AbstractAction {
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['FACEBOOK_PUBLIC_KEY', 'FACEBOOK_PRIVATE_KEY'];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (WCF::getSession()->spiderID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		$callbackURL = LinkHandler::getInstance()->getLink('FacebookAuth');

		// Work around Facebook performing an illegal substitution of the Slash
		// by '%2F' when entering redirect URI (RFC 3986 sect. 2.2, sect. 3.4)
		$callbackURL = preg_replace_callback('/(?<=\?).*/', function ($matches) {
			return rawurlencode($matches[0]);
		}, $callbackURL);

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
				\wcf\functions\exception\logThrowable($e);
				throw new IllegalLinkException();
			}
			
			// validate state, validation of state is executed after fetching the access_token to invalidate 'code'
			if (!isset($_GET['state']) || !WCF::getSession()->getVar('__facebookInit') || !\hash_equals(WCF::getSession()->getVar('__facebookInit'), $_GET['state'])) throw new IllegalLinkException();
			WCF::getSession()->unregister('__facebookInit');
			
			try {
				$data = JSON::decode($content);
			}
			catch (SystemException $e) {
				parse_str($content, $data);
			}
			
			if (!isset($data['access_token'])) throw new IllegalLinkException();
			
			try {
				// fetch userdata
				$request = new HTTPRequest('https://graph.facebook.com/me?access_token='.rawurlencode($data['access_token']).'&fields=about,birthday,email,gender,id,location,name,picture.type(large),website');
				$request->execute();
				$reply = $request->getReply();
				
				$content = $reply['body'];
			}
			catch (SystemException $e) {
				\wcf\functions\exception\logThrowable($e);
				throw new IllegalLinkException();
			}
			
			$userData = JSON::decode($content);
			
			// check whether a user is connected to this facebook account
			$user = User::getUserByAuthData('facebook:'.$userData['id']);
			
			if ($user->userID) {
				// a user is already connected, but we are logged in, break
				if (WCF::getUser()->userID) {
					throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.3rdparty.facebook.connect.error.inuse'));
				}
				// perform login
				else {
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
					if (isset($userData['email'])) WCF::getSession()->register('__email', $userData['email']);
					WCF::getSession()->register('__facebookData', $userData);
					
					// we assume that bots won't register on facebook first
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
			throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.3rdparty.facebook.login.error.'.$_GET['error']));
		}
		
		// start auth by redirecting to facebook
		$token = Hex::encode(\random_bytes(20));
		WCF::getSession()->register('__facebookInit', $token);
		HeaderUtil::redirect("https://www.facebook.com/dialog/oauth?client_id=".StringUtil::trim(FACEBOOK_PUBLIC_KEY). "&redirect_uri=".rawurlencode($callbackURL)."&state=".$token."&scope=email");
		$this->executed();
		exit;
	}
}
