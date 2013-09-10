<?php
namespace wcf\page;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Extends AbstractPage by a function to validate a given security token.
 * A missing or invalid token will be result in a throw of a IllegalLinkException.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
abstract class AbstractSecurePage extends AbstractPage {
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// check security token
		$this->checkSecurityToken();
	}
	
	/**
	 * Validates the security token.
	 */
	protected function checkSecurityToken() {
		if (!isset($_REQUEST['t']) || !WCF::getSession()->checkSecurityToken($_REQUEST['t'])) {
			throw new IllegalLinkException();
		}
	}
}
