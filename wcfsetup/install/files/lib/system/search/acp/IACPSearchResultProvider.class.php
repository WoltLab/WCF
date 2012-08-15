<?php
namespace wcf\system\search\acp;

/**
 * Default implementation for ACP search providers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category 	Community Framework
 */
interface IACPSearchResultProvider {
	/**
	 * Returns a list of seach results for given query.
	 * 
	 * @param	string		$query
	 * @param	integer		$limit
	 * @return	array<wcf\system\search\acp\ACPSearchResult>
	 */
	public function search($query, $limit = 5);
}
