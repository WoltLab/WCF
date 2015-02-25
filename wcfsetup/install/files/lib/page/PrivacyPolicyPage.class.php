<?php
namespace wcf\page;

/**
 * Show the privacy policy.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class PrivacyPolicyPage extends AbstractPage {
	const AVAILABLE_DURING_OFFLINE_MODE = true;
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_PRIVACY_POLICY_PAGE');
}
