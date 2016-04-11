<?php
namespace wcf\system\box;
use wcf\data\box\Box;

/**
 * Default implementation for box controllers.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
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
	protected $content;
	
	/**
	 * supported box positions
	 * @var string[]
	 */
	protected $supportedPositions = [];
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContent() {
		if ($this->content === null) {
			$this->content = '';
			$this->loadContent();
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
