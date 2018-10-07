<?php
namespace wcf\system\devtools\pip;

/**
 * Default implementation of a list of entries of a specific pip and specific
 * project.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Devtools\Pip
 * @since	3.2
 */
class DevtoolsPipEntryList implements IDevtoolsPipEntryList {
	/**
	 * pip entries
	 * @var	array<array>
	 */
	protected $entries = [];
	
	/**
	 * keys of the entries that can be used to display the entry list as a
	 * table
	 * @var	string[]
	 */
	protected $keys;
	
	/**
	 * @inheritDoc
	 */
	public function addEntry($id, array $entry) {
		if ($this->keys === null) {
			throw new \BadMethodCallException("No keys have been set.");
		}
		
		if (isset($this->entries[$id])) {
			throw new \InvalidArgumentException("Entry with id '{$id}' already exists.");
		}
		
		foreach ($entry as $key => $value) {
			if (!isset($this->keys[$key])) {
				throw new \InvalidArgumentException("Unknown key '{$key}'.");
			}
		}
		
		foreach ($this->keys as $key => $label) {
			if (!isset($entry[$key])) {
				$entry[$key] = '';
			}
		}
		
		$this->entries[$id] = $entry;
	}
	
	/**
	 * @inheritDoc
	 */
	public function filterEntries($filter) {
		$filterType = gettype($filter);
		
		switch ($filterType) {
			case 'array':
				$unknownFilters = array_diff(array_keys($filter), array_keys($this->keys));
				
				if (!empty($unknownFilters)) {
					throw new \InvalidArgumentException("Unknown filter" . (count($unknownFilters) > 1 ? 's' : '') . " '". implode(', ', $unknownFilters) ."'.");
				}
				
				$filteredEntries = [];
				foreach ($this->entries as $id => $entry) {
					foreach ($filter as $filterKey => $filterString) {
						if (isset($entry[$filterKey]) && strpos($entry[$filterKey], $filterString) !== false) {
							$filteredEntries[$id] = $entry;
						}
					}
				}
				
				$this->entries = $filteredEntries;
				
				break;
				
			case 'string':
				$filteredEntries = [];
				foreach ($this->entries as $id => $entry) {
					foreach ($this->keys as $key => $label) {
						if (isset($entry[$key]) && strpos($entry[$key], $filter) !== false) {
							$filteredEntries[$id] = $entry;
						}
					}
				}
				
				$this->entries = $filteredEntries;
				
				break;
				
			default:
				throw new \InvalidArgumentException("Cannot use '{$filterType}' to filter entries.");
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEntries($startIndex = null, $entryCount = null) {
		if ($startIndex !== null && $entryCount !== null) {
			return array_slice($this->entries, $startIndex, $entryCount);
		}
		
		return $this->entries;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getKeys() {
		if ($this->keys === null) {
			throw new \BadMethodCallException("No keys have been set.");
		}
		
		return $this->keys;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasEntry($id) {
		return isset($this->entries[$id]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function setKeys(array $keys) {
		if ($this->keys !== null) {
			throw new \BadMethodCallException("Keys have already been set.");
		}
		
		foreach ($keys as $key => $value) {
			if (!is_string($key)) {
				throw new \InvalidArgumentException("Given key is no string, " . gettype($key) . " given.");
			}
			
			if (!is_string($value)) {
				throw new \InvalidArgumentException("Given value is no string, " . gettype($value) . " given.");
			}
		}
		
		$this->keys = $keys;
	}
}
