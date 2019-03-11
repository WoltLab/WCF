<?php
namespace wcf\system\version;

/**
 * Generic data holder for version tracker entries.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Version
 * @since	3.1
 * 
 * @property-read	integer		$versionID		unique id of the tracked version entry
 * @property-read	integer		$objectID		id of the edited object
 * @property-read	integer|null	$userID			id of the user who has created the previous version of the object or `null` if the user does not exist anymore or if the previous version has been created by a guest
 * @property-read	string		$username		name of the user who has created the previous version of the object
 * @property-read	integer		$time			timestamp at which the original version has been created
 */
class VersionTrackerEntry {
	/**
	 * object data
	 * @var	array
	 */
	protected $data = [];
	
	/**
	 * list of stored properties and their values
	 * @var array
	 */
	protected $payload = [];
	
	/**
	 * VersionTrackerEntry constructor.
	 *
	 * @param       integer         $id             id
	 * @param       array           $data           version data
	 */
	public function __construct($id, array $data) {
		if ($id !== null) {
			throw new \InvalidArgumentException("Accessing tracked versions by id is not supported.");
		}
		
		if (isset($data['data'])) {
			$payload = (is_array($data['data'])) ? $data['data'] : @unserialize($data['data']);
			if ($payload !== false && is_array($payload)) {
				$this->payload = $payload;
			}
			
			unset($data['data']);
		}
		
		$this->data = $data;
	}
	
	/**
	 * Returns the value of a object data variable with the given name or `null` if no
	 * such data variable exists.
	 *
	 * @param	string		$name
	 * @return	mixed
	 */
	public function __get($name) {
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}
		else {
			return null;
		}
	}
	
	/**
	 * Determines if the object data variable with the given name is set and
	 * is not NULL.
	 *
	 * @param	string		$name
	 * @return	boolean
	 */
	public function __isset($name) {
		return isset($this->data[$name]);
	}
	
	/**
	 * Returns the stored value of a property or null if unknown.
	 * 
	 * @param       string          $property       property name
	 * @param       integer         $languageID     language id
	 * @return      string
	 */
	public function getPayload($property, $languageID) {
		if (isset($this->payload[$languageID])) {
			return (isset($this->payload[$languageID][$property])) ? $this->payload[$languageID][$property] : '';
		}
		
		return '';
	}
	
	/**
	 * Returns the stored values for all given properties. Unknown or missing
	 * properties will be set to an empty string.
	 * 
	 * @param       string[]        $properties     list of property names
	 * @param       integer         $languageID     language id
	 * @return      string[]
	 */
	public function getPayloadForProperties(array $properties, $languageID) {
		$payload = [];
		foreach ($properties as $property) {
			$payload[$property] = '';
			
			if (isset($this->payload[$languageID]) && isset($this->payload[$languageID][$property])) {
				$payload[$property] = $this->payload[$languageID][$property];
			}
		}
		
		return $payload;
	}
	
	/**
	 * Returns the list of language ids.
	 * 
	 * @return      integer[]
	 */
	public function getLanguageIDs() {
		return array_keys($this->payload);
	}
}
