<?php

namespace wcf\data\user\profile\menu\item;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\UserProfileMenuCacheBuilder;
use wcf\system\WCF;

/**
 * Provides functions to edit user profile menu items.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserProfileMenuItem getDecoratedObject()
 * @mixin   UserProfileMenuItem
 */
class UserProfileMenuItemEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserProfileMenuItem::class;

    /**
     * @inheritDoc
     * @return  UserProfileMenuItem
     */
    public static function create(array $parameters = [])
    {
        // calculate show order
        $parameters['showOrder'] = self::getShowOrder($parameters['showOrder'] ?? 0);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return parent::create($parameters);
    }

    /**
     * @inheritDoc
     */
    public function update(array $parameters = [])
    {
        if (isset($parameters['showOrder'])) {
            $this->updateShowOrder($parameters['showOrder']);
        }

        parent::update($parameters);
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        // update show order
        $sql = "UPDATE  wcf1_user_profile_menu_item
                SET     showOrder = showOrder - 1
                WHERE   showOrder >= ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->showOrder]);

        parent::delete();
    }

    /**
     * Updates show order for current menu item.
     *
     * @param int $showOrder
     */
    protected function updateShowOrder($showOrder)
    {
        if ($this->showOrder != $showOrder) {
            if ($showOrder < $this->showOrder) {
                $sql = "UPDATE  wcf1_user_profile_menu_item
                        SET     showOrder = showOrder + 1
                        WHERE   showOrder >= ?
                            AND showOrder < ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $showOrder,
                    $this->showOrder,
                ]);
            } elseif ($showOrder > $this->showOrder) {
                $sql = "UPDATE  wcf1_user_profile_menu_item
                        SET     showOrder = showOrder - 1
                        WHERE   showOrder <= ?
                            AND showOrder > ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $showOrder,
                    $this->showOrder,
                ]);
            }
        }
    }

    /**
     * Returns show order for a new menu item.
     *
     * @param int $showOrder
     * @return  int
     */
    protected static function getShowOrder($showOrder = 0)
    {
        if ($showOrder == 0) {
            // get next number in row
            $sql = "SELECT  MAX(showOrder) AS showOrder
                    FROM    wcf1_user_profile_menu_item";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute();
            $row = $statement->fetchArray();
            if (!empty($row)) {
                $showOrder = \intval($row['showOrder']) + 1;
            } else {
                $showOrder = 1;
            }
        } else {
            $sql = "UPDATE  wcf1_user_profile_menu_item
                    SET     showOrder = showOrder + 1
                    WHERE   showOrder >= ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$showOrder]);
        }

        return $showOrder;
    }

    /**
     * @inheritDoc
     */
    public static function resetCache()
    {
        UserProfileMenuCacheBuilder::getInstance()->reset();
    }
}
