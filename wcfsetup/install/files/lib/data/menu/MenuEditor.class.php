<?php
namespace wcf\data\menu;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\MenuCacheBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Provides functions to edit menus.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.menu
 * @category	Community Framework
 * @since	2.2
 * 
 * @method	Menu	getDecoratedObject()
 * @mixin	Menu
 */
class MenuEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Menu::class;
	
	/**
	 * @inheritDoc
	 */
	public static function create(array $parameters = []) {
		$title = '';
		if (is_array($parameters['title'])) {
			$title = $parameters['title'];
			$parameters['title'] = '';
		}
		
		$menu = parent::create($parameters);
		
		if (is_array($title)) {
			if (count($title) > 1) {
				$sql = "SELECT	languageCategoryID
					FROM	wcf".WCF_N."_language_category
					WHERE	languageCategory = ?";
				$statement = WCF::getDB()->prepareStatement($sql, 1);
				$statement->execute(['wcf.menu']);
				$languageCategoryID = $statement->fetchSingleColumn();
				
				$sql = "INSERT INTO	wcf".WCF_N."_language_item
							(languageID, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID)
					VALUES		(?, ?, ?, ?, ?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				
				WCF::getDB()->beginTransaction();
				foreach ($title as $languageCode => $value) {
					$statement->execute([
						LanguageFactory::getInstance()->getLanguageByCode($languageCode)->languageID,
						'wcf.menu.menu' . $menu->menuID,
						$value,
						1,
						$languageCategoryID,
						$menu->packageID
					]);
				}
				WCF::getDB()->commitTransaction();
				
				$title = 'wcf.menu.menu' . $menu->menuID;
			}
			else {
				$title = reset($title);
			}
			
			$menuEditor = new self($menu);
			$menuEditor->update(['title' => $title]);
			$menu = new static::$baseClass($menu->menuID);
		}
		
		return $menu;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		MenuCacheBuilder::getInstance()->reset();
	}
}
