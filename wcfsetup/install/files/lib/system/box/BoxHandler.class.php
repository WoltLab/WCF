<?php
namespace wcf\system\box;
use wcf\data\box\Box;
use wcf\data\box\BoxList;
use wcf\system\SingletonFactory;

/**
 * Handles boxes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 * @since	2.2
 */
class BoxHandler extends SingletonFactory {
	/**
	 * boxes grouped by position
	 * @var	Box[][]
	 */
	protected $boxes = [];
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		// load box layout for active page
		$boxList = new BoxList();
		$boxList->sqlOrderBy = 'showOrder';
		$boxList->readObjects();
		foreach ($boxList as $box) {
			if (!isset($this->boxes[$box->position])) $this->boxes[$box->position] = [];
			$this->boxes[$box->position][] = $box;
		}
	}
	
	/**
	 * Returns boxes for the given position.
	 * 
	 * @param	string		$position
	 * @return	Box[]
	 */
	public function getBoxes($position) {
		if (isset($this->boxes[$position])) {
			return $this->boxes[$position];
		}
		
		return [];
	}
}
