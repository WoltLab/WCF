<?php
namespace wcf\data\box;
use wcf\data\box\content\BoxContent;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\IVersionTrackerObject;
use wcf\system\request\LinkHandler;

/**
 * Represents a box with version tracking.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Box
 * @since	3.1
 * 
 * @method	Box     getDecoratedObject()
 * @mixin	Box
 */
class BoxVersionTracker extends DatabaseObjectDecorator implements IVersionTrackerObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Box::class;
	
	/**
	 * list of box content objects
	 * @var BoxContent[]
	 */
	protected $content = [];
	
	/**
	 * @inheritDoc
	 */
	public function getObjectID() {
		return $this->getDecoratedObject()->boxID;
	}
	
	/**
	 * Adds an box content object as child.
	 * 
	 * @param       BoxContent      $content        box content object
	 */
	public function addContent(BoxContent $content) {
		$this->content[] = $content;
	}
	
	/**
	 * Sets the list of box content objects.
	 * 
	 * @param       BoxContent[]    $content        box content objects
	 */
	public function setContent(array $content) {
		$this->content = $content;
	}
	
	/**
	 * Returns the list of stored box content objects.
	 * 
	 * @return      BoxContent[]    stored box content objects
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
		return LinkHandler::getInstance()->getLink('BoxEdit', ['isACP' => true, 'id' => $this->getDecoratedObject()->boxID]);
	}
}
