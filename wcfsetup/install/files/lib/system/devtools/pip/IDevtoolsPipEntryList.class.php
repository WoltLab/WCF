<?php
declare(strict_types=1);
namespace wcf\system\devtools\pip;

/**
 * Represents a list of entries of a specific pip and specific project.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Devtools\Pip
 * @since	3.2
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
	 * Returns all entries in the list.
	 * 
	 * @return	array
	 */
	public function getEntries();
	
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
	 * Sets the keys of the entries that can be used to display the entry list
	 * as a table.
	 * 
	 * @param	array		$keys		entry keys
	 */
	public function setKeys(array $keys);
}
