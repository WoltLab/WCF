<?php
namespace wcf\page;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the paid subscription return message.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class PaidSubscriptionReturnPage extends AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'redirect';
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'message' => WCF::getLanguage()->get('wcf.paidSubscription.returnMessage'),
			'wait' => 60,
			'url' => LinkHandler::getInstance()->getLink()
		));
	}
}
