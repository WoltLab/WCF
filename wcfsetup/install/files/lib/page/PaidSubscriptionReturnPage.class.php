<?php
namespace wcf\page;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the paid subscription return message.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
class PaidSubscriptionReturnPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $templateName = 'redirect';
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'message' => WCF::getLanguage()->get('wcf.paidSubscription.returnMessage'),
			'wait' => 60,
			'url' => LinkHandler::getInstance()->getLink()
		]);
	}
}
