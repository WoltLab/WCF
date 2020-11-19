<?php
namespace wcf\form;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Aborts the multi-factor authentication process.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 * @since	5.4
 */
class MultifactorAuthenticationAbortForm extends AbstractForm {
	const AVAILABLE_DURING_OFFLINE_MODE = true;
	
	/**
	 * @inheritDoc
	 */
	public $useTemplate = false;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		$user = WCF::getSession()->getPendingUserChange();
		if (!$user) {
			$this->performRedirect();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		WCF::getSession()->clearPendingUserChange();
		
		$this->saved();
	}
	
	/**
	 * @inheritDoc
	 */
	public function saved() {
		parent::saved();
		
		$this->performRedirect();
	}
	
	/**
	 * Returns to the landing page otherwise.
	 */
	protected function performRedirect() {
		HeaderUtil::delayedRedirect(
			LinkHandler::getInstance()->getLink(),
			WCF::getLanguage()->getDynamicVariable('wcf.user.security.multifactor.authentication.logout.success')
		);
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		parent::show();
		
		// It is not expected to reach this place, because the form should
		// never be accessed via a direct link.
		// If we reach it nonetheless we simply redirect back to the authentication
		// form which contains the proper button to perform the submission.
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('MultifactorAuthentication'));
		exit;
	}
}
