<?php
namespace wcf\system\user\signature;
use wcf\data\user\User;
use wcf\system\bbcode\MessageParser;
use wcf\system\SingletonFactory;

/**
 * Caches parsed user signatures.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Signature
 */
class SignatureCache extends SingletonFactory {
	/**
	 * cached signatures
	 * @var	string
	 */
	protected $signatures = [];
	
	/**
	 * Returns a parsed user signature.
	 * 
	 * @param	\wcf\data\user\User	$user
	 * @return	string
	 */
	public function getSignature(User $user) {
		if (!isset($this->signatures[$user->userID])) {
			$this->signatures[$user->userID] = MessageParser::getInstance()->parse($user->signature, $user->signatureEnableSmilies, $user->signatureEnableHtml, $user->signatureEnableBBCodes, false);
		}
		
		return $this->signatures[$user->userID];
	}
}
