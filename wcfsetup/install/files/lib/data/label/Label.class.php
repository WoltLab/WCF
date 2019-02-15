<?php
namespace wcf\data\label;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a label.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Label
 *
 * @property-read	integer		$labelID		unique id of the label
 * @property-read	integer		$groupID		id of the label group the label belongs to
 * @property-read	string		$label			label text or name of language item which contains the label text
 * @property-read	string		$cssClassName		css class name used when displaying the label
 * @property-read	integer		$showOrder		position of the label in relation to the other labels in the label group
 */
class Label extends DatabaseObject implements IRouteController {
	/**
	 * Returns the label's textual representation if a label is treated as a
	 * string.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->getTitle();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->label);
	}
	
	/**
	 * Returns label CSS class names.
	 * 
	 * @return	string
	 */
	public function getClassNames() {
		if ($this->cssClassName == 'none') {
			return '';
		}
		
		return $this->cssClassName;
	}
}
