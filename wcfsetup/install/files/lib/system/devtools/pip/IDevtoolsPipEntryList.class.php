<?php
namespace wcf\system\devtools\pip;

/**
 * Represents a list of entries of a specific pip and specific project.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Devtools\Pip
 * @since	5.2
 */
interface IDevtoolsPipEntryList {
	/**
	 * Adds an entry to the entry list.
	 * 
	 * Before adding entries, the keys must be set.
	 * 
	 * @param	string		$id		unique entry identifier
	 * @param	array		$entry		entry data
	 * @throws	\BadMethodCallException		if no keys have been set
	 */
	public function addEntry($id, array $entry);
	
	/**
	 * Internally filters the entries using the given filter.
	 * `getEntries()` will only return filter entries afterwards.
	 * 
	 * This filter is applied to the elements currently in the list.
	 * Entries added afterwards are not affected by this filter.
	 * 
	 * Applying a second filter will filter the pre-filtered entries.
	 * 
	 * @param	string|array	$filter		either a string that is used to search all entry elements or filter map `key => searchString`
	 */
	public function filterEntries($filter);
	
	/**
	 * Returns all entries in the list.
	 * 
	 * @param	int	$startIndex
	 * @param	int	$entryCount
	 * @return	array
	 */
	public function getEntries($startIndex = null, $entryCount = null);
	
	/**
	 * Returns the expected keys of the entries that can be used to display the
	 * entry list as a table.
	 * 
	 * The keys of the returned array are the entry keys and the array values are
	 * language items describing the value.
	 * 
	 * @return	array
	 * @throws	\BadMethodCallException		if no keys have been set
	 */
	public function getKeys();
	
	/**
	 * Returns true if an entry with the given entry identifier exists.
	 * 
	 * @param	string		$id	unique entry identifier
	 * @return	bool
	 */
	public function hasEntry($id);
	
	/**
	 * Sets the keys of the entries that can be used to display the entry list
	 * as a table.
	 * 
	 * @param	array		$keys		entry keys
	 */
	public function setKeys(array $keys);
}
