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
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @method  Menu    getDecoratedObject()
 * @mixin   Menu
 */
class MenuEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Menu::class;

    /**
     * @inheritDoc
     * @return  Menu
     */
    public static function create(array $parameters = [])
    {
        $title = '';
        if (\is_array($parameters['title'])) {
            $title = $parameters['title'];
            $parameters['title'] = '';
        }

        /** @var Menu $menu */
        $menu = parent::create($parameters);

        if (\is_array($title)) {
            if (\count($title) > 1) {
                $sql = "SELECT  languageCategoryID
                        FROM    wcf1_language_category
                        WHERE   languageCategory = ?";
                $statement = WCF::getDB()->prepare($sql, 1);
                $statement->execute(['wcf.menu']);
                $languageCategoryID = $statement->fetchSingleColumn();

                $sql = "INSERT INTO wcf1_language_item
                                    (languageID, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID)
                        VALUES      (?, ?, ?, ?, ?, ?)";
                $statement = WCF::getDB()->prepare($sql);

                WCF::getDB()->beginTransaction();
                foreach ($title as $languageCode => $value) {
                    $statement->execute([
                        LanguageFactory::getInstance()->getLanguageByCode($languageCode)->languageID,
                        'wcf.menu.' . $menu->identifier,
                        $value,
                        1,
                        $languageCategoryID,
                        $menu->packageID,
                    ]);
                }
                WCF::getDB()->commitTransaction();

                $title = 'wcf.menu.' . $menu->identifier;
            } else {
                $title = \reset($title);
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
    public static function deleteAll(array $objectIDs = [])
    {
        if (!empty($objectIDs)) {
            // delete language items
            $menuList = new MenuList();
            $menuList->setObjectIDs($objectIDs);
            $menuList->readObjects();

            if (\count($menuList)) {
                $sql = "DELETE FROM wcf1_language_item
                        WHERE       languageItem = ?";
                $statement = WCF::getDB()->prepare($sql);

                WCF::getDB()->beginTransaction();
                foreach ($menuList as $menu) {
                    $statement->execute(['wcf.menu.' . $menu->identifier]);
                }
                WCF::getDB()->commitTransaction();
            }
        }

        return parent::deleteAll($objectIDs);
    }

    /**
     * @inheritDoc
     */
    public static function resetCache()
    {
        MenuCacheBuilder::getInstance()->reset();
    }
}
