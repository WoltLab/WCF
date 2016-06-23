<?php
namespace wcf\system\search\acp;
use wcf\data\box\BoxList;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search result provider implementation for cms boxes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search\Acp
 */
class BoxACPSearchResultProvider implements IACPSearchResultProvider {
	/**
	 * @inheritDoc
	 */
	public function search($query) {
		if (!WCF::getSession()->getPermission('admin.content.cms.canManageBox')) {
			return [];
		}
		
		$results = [];
		
		$boxList = new BoxList();
		$boxList->getConditionBuilder()->add('box.name LIKE ?', ['%'.$query.'%']);
		$boxList->sqlLimit = 10;
		$boxList->sqlOrderBy = 'box.name';
		$boxList->readObjects();
		foreach ($boxList as $box) {
			$results[] = new ACPSearchResult($box->name, LinkHandler::getInstance()->getLink('BoxEdit', [
				'id' => $box->boxID,
			]));
		}
		
		return $results;
	}
}
