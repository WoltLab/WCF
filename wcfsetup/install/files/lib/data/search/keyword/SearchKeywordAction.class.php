<?php
namespace wcf\data\search\keyword;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISearchAction;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes keyword-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.search
 * @subpackage	data.search.keyword
 * @category	Community Framework
 */
class SearchKeywordAction extends AbstractDatabaseObjectAction implements ISearchAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\search\keyword\SearchKeywordEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('getSearchResultList');
	
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
		$list = array();
		
		// find users
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_search_keyword
			WHERE		keyword LIKE ?
			ORDER BY	searches DESC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->parameters['data']['searchString'].'%'));
		while ($row = $statement->fetchArray()) {
			$list[] = array(
				'label' => $row['keyword'],
				'objectID' => $row['keywordID']
			);
		}
		
		return $list;
	}
}
