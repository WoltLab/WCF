<?php

namespace wcf\data\user\profile\menu\item;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\system\cache\builder\UserProfileMenuCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\menu\user\profile\UserProfileMenu;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Executes user profile menu item-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserProfileMenuItem     create()
 * @method  UserProfileMenuItemEditor[] getObjects()
 * @method  UserProfileMenuItemEditor   getSingleObject()
 */
class UserProfileMenuItemAction extends AbstractDatabaseObjectAction implements ISortableAction
{
    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['getContent'];

    /**
     * menu item
     * @var UserProfileMenuItem
     */
    protected $menuItem;

    /**
     * @inheritDoc
     */
    protected $requireACP = ['updatePosition'];

    /**
     * Validates menu item.
     */
    public function validateGetContent()
    {
        $this->readString('menuItem', false, 'data');
        $this->readInteger('userID', false, 'data');
        $this->readString('containerID', false, 'data');

        $this->menuItem = UserProfileMenu::getInstance()->getMenuItem($this->parameters['data']['menuItem']);
        if ($this->menuItem === null) {
            throw new UserInputException('menuItem');
        }
        if (!$this->menuItem->getContentManager()->isVisible($this->parameters['data']['userID'])) {
            throw new PermissionDeniedException();
        }

        $user = UserProfileRuntimeCache::getInstance()->getObject($this->parameters['data']['userID']);

        if ($user === null) {
            throw new IllegalLinkException();
        }

        if ($user->isProtected()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Returns content for given menu item.
     */
    public function getContent()
    {
        $contentManager = $this->menuItem->getContentManager();

        return [
            'containerID' => $this->parameters['data']['containerID'],
            'template' => $contentManager->getContent($this->parameters['data']['userID']),
        ];
    }

    /**
     * @inheritDoc
     */
    public function validateUpdatePosition()
    {
        WCF::getSession()->checkPermissions(['admin.user.canManageUserOption']);

        if (!isset($this->parameters['data']['structure'][0])) {
            throw new UserInputException('structure');
        }

        $sql = "SELECT  menuItemID
                FROM    wcf1_user_profile_menu_item";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $menuItemIDs = [];
        while ($menuItemID = $statement->fetchColumn()) {
            $menuItemIDs[$menuItemID] = $menuItemID;
        }

        $this->parameters['data']['structure'][0] = ArrayUtil::toIntegerArray($this->parameters['data']['structure'][0]);
        foreach ($this->parameters['data']['structure'][0] as $menuItemID) {
            if (!isset($menuItemIDs[$menuItemID])) {
                throw new UserInputException('structure');
            }

            unset($menuItemIDs[$menuItemID]);
        }

        if (!empty($menuItemIDs)) {
            throw new UserInputException('structure');
        }
    }

    /**
     * @inheritDoc
     */
    public function updatePosition()
    {
        $sql = "UPDATE  wcf1_user_profile_menu_item
                SET     showOrder = ?
                WHERE   menuItemID = ?";
        $statement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        for ($i = 0, $length = \count($this->parameters['data']['structure'][0]); $i < $length; $i++) {
            $statement->execute([
                $i,
                $this->parameters['data']['structure'][0][$i],
            ]);
        }
        WCF::getDB()->commitTransaction();

        // reset cache
        UserProfileMenuCacheBuilder::getInstance()->reset();
    }
}
