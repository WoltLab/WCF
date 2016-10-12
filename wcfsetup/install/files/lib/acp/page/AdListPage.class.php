<?php
namespace wcf\acp\page;
use wcf\data\ad\AdList;
use wcf\page\MultipleLinkPage;

/**
 * Lists the available ads.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	AdList		$objectList
 */
class AdListPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.ad.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.ad.canManageAd'];
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_WCF_AD'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = AdList::class;
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'ad.showOrder';
}
