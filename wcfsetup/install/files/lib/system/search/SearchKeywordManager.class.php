<?php
namespace wcf\system\search;
use wcf\data\search\keyword\SearchKeywordAction;
use wcf\system\SingletonFactory;

/**
 * Manages the search keywords.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search
 * @deprecated  5.2
 */
class SearchKeywordManager extends SingletonFactory {
	/**
	 * Adds the given keyword.
	 * 
	 * @param	string		$keyword
	 */
	public function add($keyword) {
		(new SearchKeywordAction([], 'upsert', ['data' => [
			'keyword' => $keyword,
		]]))->executeAction();
	}
}
