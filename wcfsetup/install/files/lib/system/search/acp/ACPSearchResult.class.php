<?php
namespace wcf\system\search\acp;

/**
 * Represents an ACP search result.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category	Community Framework
 */
class ACPSearchResult {
	/**
	 * item link
	 * @var	string
	 */
	protected $link = '';
	
	/**
	 * item title
	 * @var	string
	 */
	protected $title = '';
	
	/**
	 * Creates a new ACP search result.
	 * 
	 * @param	string		$title
	 * @param	string		$link
	 */
	public function __construct($title, $link) {
		$this->title = $title;
		$this->link = $link;
	}
	
	/**
	 * Returns the item link.
	 * 
	 * @return	string
	 */
	public function getLink() {
		return $this->link;
	}
	
	/**
	 * Returns the item title.
	 * 
	 * @return	string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * @see	\wcf\system\search\acp\ACPSearchResult::getTitle()
	 */
	public function __toString() {
		return $this->getTitle();
	}
}
