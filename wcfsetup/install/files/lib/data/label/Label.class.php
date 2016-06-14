<?php
namespace wcf\data\label;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a label.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Label
 *
 * @property-read	integer		$labelID
 * @property-read	integer		$groupID
 * @property-read	string		$label
 * @property-read	string		$cssClassName
 * @property-read	integer		$showOrder
 */
class Label extends DatabaseObject implements IRouteController {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'label';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'labelID';
	
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
