<?php
namespace wcf\data\acp\search\provider;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISearchAction;
use wcf\system\exception\UserInputException;
use wcf\system\search\acp\ACPSearchHandler;
use wcf\util\StringUtil;

/**
 * Executes ACP search provider-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.search.provider
 * @category	Community Framework
 */
class ACPSearchProviderAction extends AbstractDatabaseObjectAction implements ISearchAction {
	/**
	 * @see	wcf\data\ISearchAction::validateGetSearchResultList()
	 */
	public function validateGetSearchResultList() {
		$this->readString('searchString', false, 'data');
	}
	
	/**
	 * @see	wcf\data\ISearchAction::getSearchResultList()
	 */
	public function getSearchResultList() {
		$data = array();
		$results = ACPSearchHandler::getInstance()->search($this->parameters['data']['searchString']);
		
		foreach ($results as $resultList) {
			$items = array();
			foreach ($resultList as $item) {
				$items[] = array(
					'link' => $item->getLink(),
					'title' => $item->getTitle()
				);
			}
			
			$data[] = array(
				'items' => $items,
				'title' => $resultList->getTitle()
			);
		}
		
		return $data;
	}
}
