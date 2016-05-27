<?php
namespace wcf\data\label;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\LabelCacheBuilder;
use wcf\system\WCF;

/**
 * Provides functions to edit labels.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.label
 * @category	Community Framework
 * 
 * @method	Label	getDecoratedObject()
 * @mixin	Label
 */
class LabelEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Label::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		LabelCacheBuilder::getInstance()->reset();
	}
	
	/**
	 * Adds the label to a specific position in the label group.
	 *
	 * @param	integer		$groupID
	 * @param	integer		$showOrder
	 */
	public function setShowOrder($groupID, $showOrder = 0) {
		// shift back labels in old label group with higher showOrder
		if ($this->showOrder) {
			$sql = "UPDATE	wcf".WCF_N."_label
					SET	showOrder = showOrder - 1
					WHERE	groupID = ?
						AND showOrder >= ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->groupID, $this->showOrder]);
		}
		
		// shift labels in new label group with higher showOrder
		if ($showOrder) {
			$sql = "UPDATE	wcf".WCF_N."_label
				SET	showOrder = showOrder + 1
				WHERE	groupID = ?
					AND showOrder >= ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$groupID, $showOrder]);
		}
		
		// get maximum existing show order
		$sql = "SELECT	MAX(showOrder)
			FROM	wcf".WCF_N."_label
			WHERE	groupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$groupID]);
		$maxShowOrder = $statement->fetchColumn() ?: 0;
		
		if (!$showOrder || $showOrder > $maxShowOrder) {
			$showOrder = $maxShowOrder + 1;
		}
		
		$this->update(['showOrder' => $showOrder]);
	}
}
