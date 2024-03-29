<?php

namespace wcf\system\menu\user;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\user\menu\item\UserMenuItem;

/**
 * Default implementations of a user menu item provider.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserMenuItem    getDecoratedObject()
 * @mixin   UserMenuItem
 */
class DefaultUserMenuItemProvider extends DatabaseObjectDecorator implements IUserMenuItemProvider
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserMenuItem::class;

    /**
     * @inheritDoc
     */
    public function isVisible()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        // explicit call to satisfy our interface
        return $this->getDecoratedObject()->getLink();
    }
}
