<?php
namespace wcf\system\breadcrumb;

/**
 * Represents a breadcrumb.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Breadcrumb
 */
class Breadcrumb {
	/**
	 * displayed label
	 * @var	string
	 */
	protected $label = '';
	
	/**
	 * target url
	 * @var	string
	 */
	protected $url = '';
	
	/**
	 * Creates a new Breadcrumb object.
	 * 
	 * @param	string		$label
	 * @param	string		$url
	 */
	public function __construct($label, $url) {
		$this->setLabel($label);
		$this->setURL($url);
	}
	
	/**
	 * Sets the displayed label.
	 * 
	 * @param	string		$label
	 */
	public function setLabel($label) {
		$this->label = $label;
	}
	
	/**
	 * Sets the target url.
	 * May be left empty to disable url functionality.
	 * 
	 * @param	string		$url
	 * @param	boolean		$appendSession	This parameter is unused as of version 3.0
	 */
	public function setURL($url, $appendSession = false) {
		$this->url = $url;
	}
	
	/**
	 * Returns displayed label.
	 * 
	 * @return	string
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * Returns target url.
	 * 
	 * @return	string
	 */
	public function getURL() {
		return $this->url;
	}
}
