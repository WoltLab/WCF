<?php
namespace wcf\acp\page;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\page\AbstractPage;
use wcf\system\WCF;

/**
 * Shows a list of sitemap object types. 
 * 
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\page
 * @since	3.1
 */
class SitemapListPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.maintenance.sitemap';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.management.canRebuildData'];
	
	/**
	 * @var array<ObjectType>
	 */
	public $sitemapObjectTypes = [];
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->sitemapObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.sitemap.object');
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign([
			'sitemapObjectTypes' => $this->sitemapObjectTypes
		]);
	}
}
