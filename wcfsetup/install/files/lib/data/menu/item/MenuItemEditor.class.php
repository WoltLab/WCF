<?php

namespace wcf\data\menu\item;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\MenuCacheBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Provides functions to edit menu items.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @method  MenuItem    getDecoratedObject()
 * @mixin   MenuItem
 */
class MenuItemEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = MenuItem::class;

    /**
     * @inheritDoc
     * @return  MenuItem
     */
    public static function create(array $parameters = [])
    {
        $title = '';
        if (\is_array($parameters['title'])) {
            $title = $parameters['title'];
            $parameters['title'] = '';
        }

        /** @var MenuItem $menuItem */
        $menuItem = parent::create($parameters);

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
                        'wcf.menu.item.' . $menuItem->identifier,
                        $value,
                        1,
                        $languageCategoryID,
                        $menuItem->packageID,
                    ]);
                }
                WCF::getDB()->commitTransaction();

                $title = 'wcf.menu.item.' . $menuItem->identifier;
            } else {
                $title = \reset($title);
            }

            $menuEditor = new self($menuItem);
            $menuEditor->update(['title' => $title]);
            $menuItem = new static::$baseClass($menuItem->itemID);
        }

        return $menuItem;
    }

    /**
     * @inheritDoc
     */
    public static function deleteAll(array $objectIDs = [])
    {
        if (!empty($objectIDs)) {
            // delete language items
            $menuItemList = new MenuItemList();
            $menuItemList->setObjectIDs($objectIDs);
            $menuItemList->readObjects();

            if (\count($menuItemList)) {
                $sql = "DELETE FROM wcf1_language_item
                        WHERE       languageItem = ?";
                $statement = WCF::getDB()->prepare($sql);

                WCF::getDB()->beginTransaction();
                foreach ($menuItemList as $menuItem) {
                    $statement->execute(['wcf.menu.item.' . $menuItem->identifier]);
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
