<?php
namespace wcf\system\search;
use wcf\data\search\keyword\SearchKeyword;
use wcf\data\search\keyword\SearchKeywordAction;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the search keywords.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search
 */
class SearchKeywordManager extends SingletonFactory {
	/**
	 * Adds the given keyword.
	 * 
	 * @param	string		$keyword
	 */
	public function add($keyword) {
		// search existing entry
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_search_keyword
			WHERE	keyword = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$keyword]);
		if (($object = $statement->fetchObject(SearchKeyword::class)) !== null) {
			$action = new SearchKeywordAction([$object], 'update', ['data' => [
				'searches' => $object->searches + 1,
				'lastSearchTime' => TIME_NOW
			]]);
			$action->executeAction();
		}
		else {
			$action = new SearchKeywordAction([], 'create', ['data' => [
				'keyword' => mb_substr($keyword, 0, 255),
				'searches' => 1,
				'lastSearchTime' => TIME_NOW
			]]);
			$action->executeAction();
		}
	}
}
