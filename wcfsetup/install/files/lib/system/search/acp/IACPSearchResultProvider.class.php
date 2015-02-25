<?php
namespace wcf\system\search\acp;

/**
 * Every ACP search provider has to implement this interface.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category	Community Framework
 */
interface IACPSearchResultProvider {
	/**
	 * Returns a list of seach results for given query.
	 * 
	 * @param	string		$query
	 * @return	array<\wcf\system\search\acp\ACPSearchResult>
	 */
	public function search($query);
}
