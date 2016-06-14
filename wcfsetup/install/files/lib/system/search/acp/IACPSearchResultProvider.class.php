<?php
namespace wcf\system\search\acp;

/**
 * Every ACP search provider has to implement this interface.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search\Acp
 */
interface IACPSearchResultProvider {
	/**
	 * Returns a list of seach results for given query.
	 * 
	 * @param	string		$query
	 * @return	ACPSearchResult[]
	 */
	public function search($query);
}
