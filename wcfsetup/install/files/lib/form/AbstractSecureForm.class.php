<?php
namespace wcf\form;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Extends AbstractForm by a function to validate a given security token.
 * A missing or invalid token will be result in a throw of a IllegalLinkException.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
abstract class AbstractSecureForm extends AbstractForm {
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		// check security token
		$this->checkSecurityToken();
	}
	
	/**
	 * Validates the security token.
	 */
	protected function checkSecurityToken() {
		if (!isset($_POST['t']) || !WCF::getSession()->checkSecurityToken($_POST['t'])) {
			throw new IllegalLinkException();
		}
	}
}
