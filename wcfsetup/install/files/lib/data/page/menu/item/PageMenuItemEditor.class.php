<?php
namespace wcf\data\page\menu\item;
use wcf\data\DatabaseObjectEditor;
use wcf\data\EditableCachedObject;
use wcf\system\cache\CacheHandler;
use wcf\system\WCF;

/**
 * Provides functions to edit page menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page.menu.item
 * @category 	Community Framework
 */
class PageMenuItemEditor extends DatabaseObjectEditor implements EditableCachedObject {
	/**
	 * @see	DatabaseObjectEditor::$baseClass
	 */
	protected static $baseClass = 'wcf\data\page\menu\item\PageMenuItem';
	
	/**
	 * @see	EditableObject::create()
	 * 
	 * @todo	Handle language id and create related language item
	 */
	public static function create(array $parameters = array()) {
		// calculate show order
		$parameters['showOrder'] = self::getShowOrder($parameters['showOrder'], $parameters['menuPosition']);
		
		return parent::create($parameters);
	}
	
	/**
	 * @see	EditableObject::update()
	 * 
	 * @todo	Handle language id and update related language item
	 */
	public function update(array $parameters = array()) {
		if (isset($parameters['menuPosition']) && isset($parameters['showOrder'])) {
			$this->updateShowOrder($parameters['showOrder'], $parameters['menuPosition']);
		}
		
		parent::update($parameters);
	}
	
	/**
	 * @see	EditableObject::delete()
	 */
	public function delete() {
		// update show order
		$sql = "UPDATE	wcf".WCF_N."_page_menu_item
			SET	showOrder = showOrder - 1
			WHERE	showOrder >= ?
				AND menuPosition = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->showOrder,
			$this->menuPosition
		));
		
		parent::delete();
	}
	
	/**
	 * Updates the positions of a page menu item directly.
	 *
	 * @param	integer		$menuItemID
	 * @param	string		$menuPosition
	 * @param	integer		$showOrder
	 */
	public static function setShowOrder($menuItemID, $menuPosition = 'header', $showOrder = 1) {
		// Update
		$sql = "UPDATE	wcf".WCF_N."_page_menu_item
			SET	showOrder = ?,
				menuPosition = ?
			WHERE 	menuItemID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$showOrder,
			$menuPosition,
			$menuItemID
		));
	}
	
	/**
	 * Updates show order for current menu item.
	 * 
	 * @param	integer		$showOrder
	 * @param	string		$menuPosition
	 */
	protected function updateShowOrder($showOrder, $menuPosition) {
		if ($menuPosition == $this->menuPosition) {
			if ($this->showOrder != $showOrder) {
				if ($showOrder < $this->showOrder) {
					$sql = "UPDATE	wcf".WCF_N."_page_menu_item
						SET 	showOrder = showOrder + 1
						WHERE 	showOrder >= ?
							AND showOrder < ?
							AND menuPosition = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute(array(
						$showOrder,
						$this->showOrder,
						$menuPosition
					));
				}
				else if ($showOrder > $this->showOrder) {
					$sql = "UPDATE	wcf".WCF_N."_page_menu_item
						SET	showOrder = showOrder - 1
						WHERE	showOrder <= ?
							AND showOrder > ?
							AND menuPosition = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute(array(
						$showOrder,
						$this->showOrder,
						$menuPosition
					));
				}
			}
		}
		else {
			$sql = "UPDATE	wcf".WCF_N."_page_menu_item
				SET 	showOrder = showOrder - 1
				WHERE 	showOrder >= ?
					AND menuPosition = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$this->showOrder,
				$this->menuPosition
			));
				
			$sql = "UPDATE 	wcf".WCF_N."_page_menu_item
				SET 	showOrder = showOrder + 1
				WHERE 	showOrder >= ?
					AND menuPosition = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$showOrder,
				$menuPosition
			));
		}
	}
	
	/**
	 * Returns show order for a new menu item.
	 * 
	 * @param	integer		$showOrder
	 * @param	string		$menuPosition
	 * @return	integer
	 */
	protected static function getShowOrder($showOrder, $menuPosition) {
		if ($showOrder == 0) {
			// get next number in row
			$sql = "SELECT	MAX(showOrder) AS showOrder
				FROM	wcf".WCF_N."_page_menu_item
				WHERE	menuPosition = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($menuPosition));
			$row = $statement->fetchArray();
			if (!empty($row)) $showOrder = intval($row['showOrder']) + 1;
			else $showOrder = 1;
		}
		else {
			$sql = "UPDATE	wcf".WCF_N."_page_menu_item
				SET 	showOrder = showOrder + 1
				WHERE 	showOrder >= ?
					AND menuPosition = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$showOrder,
				$menuPosition
			));
		}
		
		return $showOrder;
	}
	
	/**
	 * Clears the page menu cache.
	 */
	public static function resetCache() {
		CacheHandler::getInstance()->clear(WCF_DIR.'cache', 'cache.pageMenu-*.php');
	}
}
?>
