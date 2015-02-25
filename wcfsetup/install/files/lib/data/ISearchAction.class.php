<?php
namespace wcf\data;

/**
 * Every database object action whose objects can be searched via AJAX has to
 * implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
interface ISearchAction {
	/**
	 * Returns a list with data of objects that match the given search criteria.
	 * 
	 * @return	array<array>
	 */
	public function getSearchResultList();
	
	/**
	 * Validates the "getSearchResultList" action.
	 */
	public function validateGetSearchResultList();
}
