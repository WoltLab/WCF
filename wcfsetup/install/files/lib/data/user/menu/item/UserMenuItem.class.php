<?php

namespace wcf\data\user\menu\item;

use wcf\data\ITitledObject;
use wcf\data\ProcessibleDatabaseObject;
use wcf\system\event\EventHandler;
use wcf\system\menu\ITreeMenuItem;
use wcf\system\menu\user\DefaultUserMenuItemProvider;
use wcf\system\menu\user\IUserMenuItemProvider;
use wcf\system\Regex;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\style\IFontAwesomeIcon;
use wcf\system\WCF;

/**
 * Represents a user menu item.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $menuItemID     unique id of the user menu item
 * @property-read   int $packageID      id of the package the which delivers the user menu item
 * @property-read   string $menuItem       textual identifier of the user menu item
 * @property-read   string $parentMenuItem     textual identifier of the menu item's parent menu item or empty if it has no parent menu item
 * @property-read   string $menuItemController class name of the user menu item's controller used to generate menu item link
 * @property-read   string $menuItemLink       additional part of the user menu item link if `$menuItemController` is set or external link
 * @property-read   int $showOrder      position of the user menu item in relation to its siblings
 * @property-read   string $permissions        comma separated list of user group permissions of which the active user needs to have at least one to see the user menu item
 * @property-read   string $options        comma separated list of options of which at least one needs to be enabled for the user menu item to be shown
 * @property-read   string $className      name of the class implementing the user menu item provider interface or empty if there is no specific user menu item provider
 * @property-read   string $iconClassName      FontAwesome CSS class name for user menu items on the first level
 */
class UserMenuItem extends ProcessibleDatabaseObject implements ITitledObject, ITreeMenuItem
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'menuItemID';

    /**
     * @inheritDoc
     */
    protected static $processorInterface = IUserMenuItemProvider::class;

    /**
     * application abbreviation
     * @var string
     */
    protected $application = '';

    /**
     * menu item controller
     * @var string
     */
    protected $controller;

    /**
     * @inheritDoc
     */
    public function getProcessor()
    {
        if (parent::getProcessor() === null) {
            $this->processor = new DefaultUserMenuItemProvider($this);
        }

        return $this->processor;
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        // external link
        if (!$this->menuItemController) {
            return $this->menuItemLink;
        }

        $this->parseController();

        return LinkHandler::getInstance()->getLink(
            $this->controller,
            ['application' => $this->application],
            $this->menuItemLink
        );
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->menuItem);
    }

    /**
     * Returns application abbreviation.
     *
     * @return  string
     */
    public function getApplication()
    {
        $this->parseController();

        return $this->application;
    }

    /**
     * Returns controller name.
     *
     * @return  string
     */
    public function getController()
    {
        $this->parseController();

        return $this->controller;
    }

    /**
     * Parses controller name.
     */
    protected function parseController()
    {
        if ($this->controller === null) {
            $this->controller = '';

            // resolve application and controller
            if ($this->menuItemController) {
                $parts = \explode('\\', $this->menuItemController);
                $this->application = \array_shift($parts);
                $menuItemController = \array_pop($parts);

                // drop controller suffix
                $this->controller = Regex::compile('(Action|Form|Page)$')->replace($menuItemController, '');
            }
        }
    }

    /**
     * Returns the menu item name.
     */
    public function __toString(): string
    {
        return WCF::getLanguage()->getDynamicVariable($this->menuItem);
    }

    /**
     * @since 6.0
     */
    public function getIcon(): IFontAwesomeIcon
    {
        if ($this->iconClassName && !\str_starts_with($this->iconClassName, 'fa-')) {
            $icon = FontAwesomeIcon::fromString($this->iconClassName);
        } else {
            $icon = FontAwesomeIcon::fromValues('bars');
        }

        $event = new \wcf\event\user\menu\item\IconResolving($this, $icon);
        EventHandler::getInstance()->fire($event);

        return $event->icon;
    }
}
