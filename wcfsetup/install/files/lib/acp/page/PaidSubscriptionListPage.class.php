<?php
namespace wcf\acp\page;
use wcf\data\paid\subscription\PaidSubscriptionList;
use wcf\page\SortablePage;

/**
 * Shows the list of paid subscriptions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	PaidSubscriptionList	$objectList
 */
class PaidSubscriptionListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.paidSubscription.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_PAID_SUBSCRIPTION'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.paidSubscription.canManageSubscription'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'showOrder';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['subscriptionID', 'title', 'showOrder', 'cost', 'subscriptionLength'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = PaidSubscriptionList::class;
}
