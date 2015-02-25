<?php
namespace wcf\data\acp\search\provider;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISearchAction;
use wcf\system\search\acp\ACPSearchHandler;

/**
 * Executes ACP search provider-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.search.provider
 * @category	Community Framework
 */
class ACPSearchProviderAction extends AbstractDatabaseObjectAction implements ISearchAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('getSearchResultList');
	
	/**
	 * @see	\wcf\data\ISearchAction::validateGetSearchResultList()
	 */
	public function validateGetSearchResultList() {
		$this->readString('searchString', false, 'data');
	}
	
	/**
	 * @see	\wcf\data\ISearchAction::getSearchResultList()
	 */
	public function getSearchResultList() {
		$data = array();
		$results = ACPSearchHandler::getInstance()->search($this->parameters['data']['searchString']);
		
		foreach ($results as $resultList) {
			$items = array();
			foreach ($resultList as $item) {
				$items[] = array(
					'link' => $item->getLink(),
					'subtitle' => $item->getSubtitle(),
					'title' => $item->getTitle()
				);
			}
			
			foreach ($items as $key => &$item) {
				$double = false;
				foreach ($items as $key2 => $item2) {
					if ($key != $key2 && $item['title'] == $item2['title']) {
						$double = true;
						break;
					}
				}
				
				if (!$double) {
					unset($item['subtitle']);
				}
			}
			unset($item);
			
			$data[] = array(
				'items' => $items,
				'title' => $resultList->getTitle()
			);
		}
		
		return $data;
	}
}
