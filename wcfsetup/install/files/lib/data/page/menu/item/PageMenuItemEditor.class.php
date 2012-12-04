<?php
namespace wcf\data\page\menu\item;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\CacheHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\package\PackageDependencyHandler;
use wcf\system\WCF;

/**
 * Provides functions to edit page menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page.menu.item
 * @category	Community Framework
 */
class PageMenuItemEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\page\menu\item\PageMenuItem';
	
	/**
	 * @see	wcf\data\IEditableObject::create()
	 * 
	 * @todo Handle language id and create related language item
	 */
	public static function create(array $parameters = array()) {
		// calculate show order
		$parameters['showOrder'] = self::getShowOrder($parameters['showOrder'], $parameters['menuPosition']);
		
		return parent::create($parameters);
	}
	
	/**
	 * @see	wcf\data\IEditableObject::update()
	 * 
	 * @todo Handle language id and update related language item
	 */
	public function update(array $parameters = array()) {
		if (isset($parameters['menuPosition']) && isset($parameters['showOrder'])) {
			$this->updateShowOrder($parameters['showOrder'], $parameters['menuPosition']);
		}
		
		parent::update($parameters);
	}
	
	/**
	 * @see	wcf\data\IEditableObject::delete()
	 */
	public function delete() {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("packageID IN (?)", array(PackageDependencyHandler::getInstance()->getDependencies()));
		$conditions->add("showOrder >= ?", array($this->showOrder));
		$conditions->add("menuPosition = ?", array($this->menuPosition));
		
		// update show order
		$sql = "UPDATE	wcf".WCF_N."_page_menu_item
			SET	showOrder = showOrder - 1
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
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
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("packageID IN (?)", array(PackageDependencyHandler::getInstance()->getDependencies()));
		$conditions->add("menuItemID = ?", array($menuItemID));
		
		// update show order
		$sql = "UPDATE	wcf".WCF_N."_page_menu_item
			SET	showOrder = ?,
				menuPosition = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		
		$parameters = array_merge(array(
			$showOrder,
			$menuPosition
		), $conditions->getParameters());
		$statement->execute($parameters);
	}
	
	/**
	 * Sets current page menu item as landing page.
	 */
	public function setAsLandingPage() {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("packageID IN (?)", array(PackageDependencyHandler::getInstance()->getDependencies()));
		
		$sql = "UPDATE	wcf".WCF_N."_page_menu_item
			SET	isLandingPage = 0
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$this->update(array('isLandingPage' => 1));
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
				$conditions = new PreparedStatementConditionBuilder();
				$conditions->add("packageID IN (?)", array(PackageDependencyHandler::getInstance()->getDependencies()));
				$conditions->add("showOrder <= ?", array($showOrder));
				$conditions->add("showOrder > ?", array($this->showOrder));
				$conditions->add("menuPosition = ?", array($menuPosition));
				
				if ($showOrder < $this->showOrder) {
					$sql = "UPDATE	wcf".WCF_N."_page_menu_item
						SET 	showOrder = showOrder + 1
						".$conditions;
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute($conditions->getParameters());
				}
				else if ($showOrder > $this->showOrder) {
					$sql = "UPDATE	wcf".WCF_N."_page_menu_item
						SET	showOrder = showOrder - 1
						WHERE	showOrder <= ?
							AND showOrder > ?
							AND menuPosition = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute($conditions->getParameters());
				}
			}
		}
		else {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("packageID IN (?)", array(PackageDependencyHandler::getInstance()->getDependencies()));
			$conditions->add("showOrder >= ?", array($this->showOrder));
			$conditions->add("menuPosition = ?", array($this->menuPosition));
			
			$sql = "UPDATE	wcf".WCF_N."_page_menu_item
				SET 	showOrder = showOrder - 1
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("packageID IN (?)", array(PackageDependencyHandler::getInstance()->getDependencies()));
			$conditions->add("showOrder >= ?", array($showOrder));
			$conditions->add("menuPosition = ?", array($menuPosition));
			
			$sql = "UPDATE 	wcf".WCF_N."_page_menu_item
				SET 	showOrder = showOrder + 1
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
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
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("packageID IN (?)", array(PackageDependencyHandler::getInstance()->getDependencies()));
			$conditions->add("menuPosition = ?", array($menuPosition));
			
			// get next number in row
			$sql = "SELECT	MAX(showOrder) AS showOrder
				FROM	wcf".WCF_N."_page_menu_item
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			$row = $statement->fetchArray();
			if (!empty($row)) $showOrder = intval($row['showOrder']) + 1;
			else $showOrder = 1;
		}
		else {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("packageID IN (?)", array(PackageDependencyHandler::getInstance()->getDependencies()));
			$conditions->add("showOrder >= ?", array($showOrder));
			$conditions->add("menuPosition = ?", array($menuPosition));
			
			$sql = "UPDATE	wcf".WCF_N."_page_menu_item
				SET 	showOrder = showOrder + 1
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
		}
		
		return $showOrder;
	}
	
	/**
	 * @see	wcf\data\IEditableCachedObject::resetCache()
	 */
	public static function resetCache() {
		CacheHandler::getInstance()->clear(WCF_DIR.'cache', 'cache.pageMenu-*.php');
	}
}
