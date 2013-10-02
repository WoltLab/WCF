<?php
namespace wcf\form;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Extends AbstractForm by a function to validate a given security token.
 * A missing or invalid token will be result in a throw of a UserInputException.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
abstract class AbstractSecureForm extends AbstractForm {
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		$this->checkSecurityToken();
	}
	
	/**
	 * Validates the security token.
	 */
	protected function checkSecurityToken() {
		if (!isset($_POST['t']) || !WCF::getSession()->checkSecurityToken($_POST['t'])) {
			throw new UserInputException('__securityToken');
		}
	}
}
