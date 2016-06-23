<?php
namespace wcf\system\search\acp;
use wcf\data\page\PageList;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search result provider implementation for cms pages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search\Acp
 */
class PageACPSearchResultProvider implements IACPSearchResultProvider {
	/**
	 * @inheritDoc
	 */
	public function search($query) {
		if (!WCF::getSession()->getPermission('admin.content.cms.canManagePage')) {
			return [];
		}
		
		$results = [];
		
		$pageList = new PageList();
		$pageList->getConditionBuilder()->add('page.name LIKE ?', ['%'.$query.'%']);
		$pageList->sqlLimit = 10;
		$pageList->sqlOrderBy = 'page.name';
		$pageList->readObjects();
		foreach ($pageList as $page) {
			$results[] = new ACPSearchResult($page->name, LinkHandler::getInstance()->getLink('PageEdit', [
				'id' => $page->pageID,
			]));
		}
		
		return $results;
	}
}
