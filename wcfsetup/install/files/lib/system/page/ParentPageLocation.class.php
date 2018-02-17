<?php
namespace wcf\system\page;
use wcf\data\ITitledLinkObject;

/**
 * Parent page location representation implementing the required `ITitledLinkObject` interface
 * to properly handle certain edge cases. 
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page
 * @since	3.0
 */
class ParentPageLocation implements ITitledLinkObject {
	/**
	 * link of the parent page location
	 * @var	string
	 */
	public $link;
	
	/**
	 * title of the parent page location
	 * @var	string
	 */
	protected $title;
	
	/**
	 * ParentPageLocation constructor.
	 * 
	 * @param	string		$title		title of the parent page location
	 * @param	string		$link		link of the parent page location
	 */
	public function __construct($title, $link) {
		$this->title = $title;
		$this->link = $link;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return $this->link;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->title;
	}
}
