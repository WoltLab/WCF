<?php
namespace wcf\system\clipboard;
use wcf\system\exception\SystemException;

/**
 * Represents a clipboard item for inline editing.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Clipboard
 */
final class ClipboardEditorItem {
	/**
	 * internal data
	 * @var	array
	 */
	protected $internalData = [];
	
	/**
	 * item name
	 * @var	string
	 */
	protected $name = '';
	
	/**
	 * list of parameters passed to ClipboardProxyAction
	 * @var	array
	 */
	protected $parameters = [];
	
	/**
	 * redirect url
	 * @var	string
	 */
	protected $url = '';
	
	/**
	 * Returns internal data.
	 * 
	 * @return	array
	 */
	public function getInternalData() {
		return $this->internalData;
	}
	
	/**
	 * Returns item name.
	 * 
	 * @return	string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Returns parameters passed to ClipboardProxyAction.
	 * 
	 * @return	array
	 */
	public function getParameters() {
		return $this->parameters;
	}
	
	/**
	 * Returns redirect url.
	 * 
	 * @return	string
	 */
	public function getURL() {
		return $this->url;
	}
	
	/**
	 * Adds internal data, values will be left untouched by clipboard API.
	 * 
	 * @param	string		$name
	 * @param	mixed		$value
	 * @throws	SystemException
	 */
	public function addInternalData($name, $value) {
		if (!preg_match('~^[a-zA-Z]+$~', $name)) {
			throw new SystemException("internal data name '".$name."' is invalid");
		}
		
		if (in_array($name, $this->internalData)) {
			throw new SystemException("internal data name '".$name."' is not unique");
		}
		
		$this->internalData[$name] = $value;
	}
	
	/**
	 * Adds an parameter passed to ClipboardProxyAction.
	 * 
	 * @param	string		$name
	 * @param	mixed		$value
	 * @throws	SystemException
	 */
	public function addParameter($name, $value) {
		if (!preg_match('~^[a-zA-Z]+$~', $name)) {
			throw new SystemException("parameter name '".$name."' is invalid");
		}
		
		if (in_array($name, $this->parameters)) {
			throw new SystemException("parameter name '".$name."' is not unique");
		}
		
		$this->parameters[$name] = $value;
	}
	
	/**
	 * Sets item name.
	 * 
	 * @param	string		$name
	 * @throws	SystemException
	 */
	public function setName($name) {
		if (!preg_match('~^[a-zA-Z0-9\.-]+$~', $name)) {
			throw new SystemException("item name '".$name."' is invalid");
		}
		
		$this->name = $name;
	}
	
	/**
	 * Sets redirect url, session id will be appended.
	 * 
	 * @param	string		$url
	 */
	public function setURL($url) {
		$this->url = $url;
	}
	
	/**
	 * Returns number of affected items.
	 * 
	 * @return	integer
	 */
	public function getCount() {
		if (isset($this->parameters['objectIDs'])) {
			return count($this->parameters['objectIDs']);
		}
		
		return 0;
	}
}
