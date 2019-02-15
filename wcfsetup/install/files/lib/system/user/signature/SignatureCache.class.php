<?php
namespace wcf\system\user\signature;
use wcf\data\user\User;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\SingletonFactory;

/**
 * Caches parsed user signatures.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Signature
 */
class SignatureCache extends SingletonFactory {
	/**
	 * @var HtmlOutputProcessor
	 */
	protected $htmlOutputProcessor;
	
	/**
	 * cached signatures
	 * @var	string
	 */
	protected $signatures = [];
	
	/**
	 * Returns a parsed user signature.
	 * 
	 * @param	User	        $user           user object
	 * @return	string          parsed signature
	 */
	public function getSignature(User $user) {
		if (!isset($this->signatures[$user->userID])) {
			if ($this->htmlOutputProcessor === null) {
				$this->htmlOutputProcessor = new HtmlOutputProcessor();
			}
			
			MessageEmbeddedObjectManager::getInstance()->loadObjects('com.woltlab.wcf.user.signature', [$user->userID]);
			
			$this->htmlOutputProcessor->setContext('com.woltlab.wcf.user.signature', $user->userID);
			$this->htmlOutputProcessor->process($user->signature, 'com.woltlab.wcf.user.signature', $user->userID);
			$this->signatures[$user->userID] = $this->htmlOutputProcessor->getHtml();
		}
		
		return $this->signatures[$user->userID];
	}
}
