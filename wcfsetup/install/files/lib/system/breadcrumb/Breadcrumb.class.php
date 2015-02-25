<?php
namespace wcf\system\breadcrumb;

/**
 * Represents a breadcrumb.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.breadcrumb
 * @category	Community Framework
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
	 * Sets the target url, by default appends appropriate session id.
	 * May be left empty to disable url functionality.
	 * 
	 * @param	string		$url
	 * @param	boolean		$appendSession
	 */
	public function setURL($url, $appendSession = false) {
		// append session id
		if ($appendSession) {
			if (mb_strpos($url, '?') === false) {
				$url .= SID_ARG_1ST;
			}
			else {
				$url .= SID_ARG_2ND_NOT_ENCODED;
			}
		}
		
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
