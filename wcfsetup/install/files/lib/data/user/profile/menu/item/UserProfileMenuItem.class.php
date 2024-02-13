<?php

namespace wcf\data\user\profile\menu\item;

use wcf\data\DatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;
use wcf\system\exception\ClassNotFoundException;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\ParentClassException;
use wcf\system\exception\SystemException;
use wcf\system\menu\user\profile\content\IUserProfileMenuContent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Represents a user profile menu item.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $menuItemID     unique id of the user profile menu item
 * @property-read   int $packageID      id of the package which delivers the user profile menu item
 * @property-read   string $menuItem       textual identifier of the user profile menu item
 * @property-read   int $showOrder      position of the user profile menu item in relation to its siblings
 * @property-read   string $permissions        comma separated list of user group permissions of which the active user needs to have at least one to see the user profile menu item
 * @property-read   string $options        comma separated list of options of which at least one needs to be enabled for the user profile menu item to be shown
 * @property-read   string $className      name of the PHP class implementing `wcf\system\menu\user\profile\content\IUserProfileMenuContent` handling outputting the content of the user profile tab
 */
class UserProfileMenuItem extends DatabaseObject
{
    use TDatabaseObjectOptions;
    use TDatabaseObjectPermissions;

    /**
     * content manager
     * @var IUserProfileMenuContent
     */
    protected $contentManager;

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'menuItemID';

    /**
     * Returns the item identifier, dots are replaced by underscores.
     *
     * @return  string
     */
    public function getIdentifier()
    {
        return \str_replace('.', '_', $this->menuItem);
    }

    /**
     * Returns the content manager for this menu item.
     *
     * @return  IUserProfileMenuContent
     * @throws  SystemException
     */
    public function getContentManager()
    {
        if ($this->contentManager === null) {
            if (!\class_exists($this->className)) {
                throw new ClassNotFoundException($this->className);
            }

            if (!\is_subclass_of($this->className, SingletonFactory::class)) {
                throw new ParentClassException($this->className, SingletonFactory::class);
            }

            if (!\is_subclass_of($this->className, IUserProfileMenuContent::class)) {
                throw new ImplementationException($this->className, IUserProfileMenuContent::class);
            }

            $this->contentManager = \call_user_func([$this->className, 'getInstance']);
        }

        return $this->contentManager;
    }

    public function __toString(): string
    {
        return WCF::getLanguage()->get('wcf.user.profile.menu.' . $this->menuItem);
    }
}
