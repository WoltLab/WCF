<?php
namespace wcf\data\paid\subscription;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\PaidSubscriptionCacheBuilder;
use wcf\system\WCF;

/**
 * Provides functions to edit paid subscriptions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.paid.subscription
 * @category	Community Framework
 *
 * @method	PaidSubscription	getDecoratedObject()
 * @mixin	PaidSubscription
 */
class PaidSubscriptionEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = PaidSubscription::class;
	
	/**
	 * Sets the show order of the subscription.
	 * 
	 * @param	integer		$showOrder
	 */
	public function setShowOrder($showOrder = 0) {
		$newShowOrder = 1;
		
		$sql = "SELECT	MAX(showOrder)
			FROM	wcf".WCF_N."_paid_subscription";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$maxShowOrder = $statement->fetchColumn();
		if (!$maxShowOrder) $maxShowOrder = 0;
		
		if (!$showOrder || $showOrder > $maxShowOrder) {
			$newShowOrder = $maxShowOrder + 1;
		}
		else {
			// shift other subscriptions
			$sql = "UPDATE	wcf".WCF_N."_paid_subscription
				SET	showOrder = showOrder + 1
				WHERE	showOrder >= ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				$showOrder
			]);
			
			$newShowOrder = $showOrder;
		}
		
		$this->update(['showOrder' => $newShowOrder]);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		PaidSubscriptionCacheBuilder::getInstance()->reset();
	}
}
