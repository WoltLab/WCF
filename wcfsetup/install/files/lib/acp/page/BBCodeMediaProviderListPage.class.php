<?php
namespace wcf\acp\page;
use wcf\data\bbcode\media\provider\BBCodeMediaProviderList;
use wcf\page\SortablePage;

/**
 * Lists the available media providers.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	BBCodeMediaProviderList		$objectList
 */
class BBCodeMediaProviderListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.bbcode.mediaProvider.list';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'title';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.bbcode.canManageBBCode'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = BBCodeMediaProviderList::class;
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'bbcodeMediaProviderList';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['providerID', 'title'];
}
