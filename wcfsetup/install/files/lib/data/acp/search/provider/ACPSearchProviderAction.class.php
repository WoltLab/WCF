<?php
namespace wcf\data\acp\search\provider;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISearchAction;
use wcf\system\search\acp\ACPSearchHandler;

/**
 * Executes ACP search provider-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Search\Provider
 * 
 * @method	ACPSearchProvider		create()
 * @method	ACPSearchProviderEditor[]	getObjects()
 * @method	ACPSearchProviderEditor		getSingleObject()
 */
class ACPSearchProviderAction extends AbstractDatabaseObjectAction implements ISearchAction {
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['getSearchResultList'];
	
	/**
	 * @inheritDoc
	 */
	public function validateGetSearchResultList() {
		$this->readString('searchString', false, 'data');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSearchResultList() {
		$data = [];
		$results = ACPSearchHandler::getInstance()->search($this->parameters['data']['searchString'], 10, (!empty($this->parameters['data']['providerName']) ? $this->parameters['data']['providerName'] : ''));
		
		foreach ($results as $resultList) {
			$items = [];
			foreach ($resultList as $item) {
				$items[] = [
					'link' => $item->getLink(),
					'subtitle' => $item->getSubtitle(),
					'title' => $item->getTitle()
				];
			}
			
			foreach ($items as $key => &$item) {
				$double = false;
				foreach ($items as $key2 => $item2) {
					if ($key != $key2 && !strcasecmp($item['title'], $item2['title'])) {
						$double = true;
						break;
					}
				}
				
				if (!$double) {
					unset($item['subtitle']);
				}
			}
			unset($item);
			
			$data[] = [
				'items' => $items,
				'title' => $resultList->getTitle()
			];
		}
		
		return $data;
	}
}
