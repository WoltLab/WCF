<?php
namespace wcf\system\box;
use wcf\data\box\Box;
use wcf\system\event\EventHandler;

/**
 * Default implementation for box controllers.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 * @since	3.0
 */
abstract class AbstractBoxController implements IBoxController {
	/**
	 * database object of this box
	 * @var Box
	 */
	protected $box;
	
	/**
	 * box content
	 * @var string
	 */
	public $content;
	
	/**
	 * supported box positions
	 * @var string[]
	 */
	protected $supportedPositions = [];
	
	/**
	 * Creates a new instance of AbstractBoxController.
	 */
	public function __construct() {
		EventHandler::getInstance()->fireAction($this, '__construct');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContent() {
		if ($this->content === null) {
			$this->content = '';
			
			EventHandler::getInstance()->fireAction($this, 'beforeLoadContent');
			
			$this->loadContent();
			
			EventHandler::getInstance()->fireAction($this, 'afterLoadContent');
		}
		
		return $this->content;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasContent() {
		return !empty($this->getContent());
	}
	
	/**
	 * @inheritDoc
	 */
	public function getImage() {
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasImage() {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasLink() {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getBox() {
		return $this->box;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setBox(Box $box) {
		$this->box = $box;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSupportedPositions() {
		if (!empty($this->supportedPositions)) {
			return $this->supportedPositions;
		}
		
		return Box::$availablePositions;
	}
	
	/**
	 * Loads the content of this box.
	 */
	abstract protected function loadContent();
}
