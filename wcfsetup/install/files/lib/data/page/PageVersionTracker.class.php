<?php
namespace wcf\data\page;
use wcf\data\page\content\PageContent;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\IVersionTrackerObject;
use wcf\system\request\LinkHandler;

/**
 * Represents a page with version tracking.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Page
 * @since	3.1
 * 
 * @method	Page    getDecoratedObject()
 * @mixin	Page
 */
class PageVersionTracker extends DatabaseObjectDecorator implements IVersionTrackerObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Page::class;
	
	/**
	 * list of page content objects
	 * @var PageContent[]
	 */
	protected $content = [];
	
	/**
	 * @inheritDoc
	 */
	public function getObjectID() {
		return $this->getDecoratedObject()->pageID;
	}
	
	/**
	 * Adds an page content object as child.
	 * 
	 * @param       PageContent     $content        page content object
	 */
	public function addContent(PageContent $content) {
		$this->content[] = $content;
	}
	
	/**
	 * Sets the list of page content objects.
	 * 
	 * @param       PageContent[]   $content        page content objects
	 */
	public function setContent(array $content) {
		$this->content = $content;
	}
	
	/**
	 * Returns the list of stored page content objects.
	 * 
	 * @return      PageContent[]   stored page content objects
	 */
	public function getContent() {
		return $this->content;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return $this->getDecoratedObject()->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUsername() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUserID() {
		return 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTime() {
		return 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getDecoratedObject()->getTitle();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEditLink() {
		return LinkHandler::getInstance()->getLink('PageEdit', ['isACP' => true, 'id' => $this->getDecoratedObject()->pageID]);
	}
}
