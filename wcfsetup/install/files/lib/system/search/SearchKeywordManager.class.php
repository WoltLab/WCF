<?php
namespace wcf\system\search;
use wcf\data\search\keyword\SearchKeywordAction;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the search keywords.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.search
 * @subpackage	system.search
 * @category	Community Framework
 */
class SearchKeywordManager extends SingletonFactory {
	/**
	 * Adds the given keyword.
	 * 
	 * @param	string		$keyword
	 */
	public function add($keyword) {
		$keyword = static::simplifyKeyword($keyword);
		
		// search existing entry
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_search_keyword
			WHERE	keyword = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($keyword));
		if (($object = $statement->fetchObject('wcf\data\search\keyword\SearchKeyword')) !== null) {
			$action = new SearchKeywordAction(array($object), 'update', array('data' => array(
				'searches' => $object->searches + 1,
				'lastSearchTime' => TIME_NOW
			)));
			$action->executeAction();
		}
		else {
			$action = new SearchKeywordAction(array(), 'create', array('data' => array(
				'keyword' => $keyword,
				'searches' => 1,
				'lastSearchTime' => TIME_NOW
			)));
			$action->executeAction();
		}
	}
	
	/**
	 * Returns simplified version of the given keyword.
	 * 
	 * @param	string		$keyword
	 * @return	string
	 */
	public static function simplifyKeyword($keyword) {
		// TODO: do something useful
		
		return $keyword;
	}
}
