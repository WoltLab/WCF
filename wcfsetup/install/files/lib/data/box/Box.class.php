<?php

namespace wcf\data\box;

use wcf\data\box\content\BoxContent;
use wcf\data\condition\Condition;
use wcf\data\DatabaseObject;
use wcf\data\media\ViewableMedia;
use wcf\data\menu\Menu;
use wcf\data\menu\MenuCache;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\page\Page;
use wcf\data\page\PageCache;
use wcf\system\acl\simple\SimpleAclResolver;
use wcf\system\box\IBoxController;
use wcf\system\box\IConditionBoxController;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\ImplementationException;
use wcf\system\page\handler\ILookupPageHandler;
use wcf\system\page\handler\IMenuPageHandler;
use wcf\system\WCF;

/**
 * Represents a box.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @property-read   int $boxID          unique id of the box
 * @property-read   int|null $objectTypeID       id of the box controller object type
 * @property-read   string $identifier     unique textual identifier of the box
 * @property-read   string $name           monolingual name of the box shown in the ACP
 * @property-read   string $boxType        type of the box which determines the method of outputting its content (default box types are `text`, `html`, `tpl`, `system`)
 * @property-read   string $position       name of the position on the page at which the box is shown
 * @property-read   int $showOrder      position of the box in relation to its siblings
 * @property-read   int $visibleEverywhere  is `1` if the box is visible on every page, otherwise `0`
 * @property-read   int $isMultilingual     is `1` if the box content is available in multiple languages, otherwise `0`
 * @property-read   int $lastUpdateTime     timestamp at which the box has been updated the last time
 * @property-read   string $cssClassName       css class name(s) of the box
 * @property-read   int $showHeader     is `1` if the box header will be shown, otherwise `0`
 * @property-read   int $originIsSystem     is `1` if the box has been delivered by a package, otherwise `0` (i.e. the box has been created in the ACP)
 * @property-read   int $packageID      id of the package which delivers the box or `1` if it has been created in the ACP
 * @property-read   int|null $menuID         id of the menu whose menu items are shown in the contents if `$boxType = menu`, otherwise `null`
 * @property-read   int|null $linkPageID     id of the (internal) page the box image and box title are linking to or `null` if no internal page is linked
 * @property-read   int $linkPageObjectID   id of the object the (internal) page links refers to or `0` if no internal link is used or no specific object is linked
 * @property-read   string $externalURL        external link used to for the box image and box title or empty if no external link is set
 * @property-read   array $additionalData     array with additional data of the box
 * @property-read   int|null $limit          number of objects shown in the box for `AbstractDatabaseObjectListBoxController` controllers or `null` otherwise
 * @property-read   string|null $sortField      sort field of the objects shown in the box for `AbstractDatabaseObjectListBoxController` controllers or `null` otherwise
 * @property-read   string|null $sortOrder      sort order of the objects shown in the box for `AbstractDatabaseObjectListBoxController` controllers or `null` otherwise
 * @property-read   int $isDisabled     is `1` if the box is disabled and thus is not displayed, otherwise `0`
 * @property-read   bool $invertPermissions     is `1` if the permissions are inverted
 */
class Box extends DatabaseObject
{
    /**
     * @since 5.5
     */
    public const VISIBILITY_CONDITIONS_OBJECT_TYPE_NAME = 'com.woltlab.wcf.condition.box';

    /**
     * image media object
     * @var ViewableMedia
     */
    protected $image;

    /**
     * available box types
     * @var string[]
     */
    public static $availableBoxTypes = ['text', 'html', 'tpl', 'system'];

    /**
     * available box positions
     * @var string[]
     */
    public static $availablePositions = [
        'hero',
        'headerBoxes',
        'top',
        'sidebarLeft',
        'contentTop',
        'sidebarRight',
        'contentBottom',
        'bottom',
        'footerBoxes',
        'footer',
    ];

    /**
     * available menu positions
     * @var string[]
     */
    public static $availableMenuPositions = ['top', 'sidebarLeft', 'sidebarRight', 'bottom', 'footer'];

    /**
     * menu object
     * @var Menu
     */
    protected $menu;

    /**
     * box to page assignments
     * @var int[]
     */
    protected $pageIDs;

    /**
     * box controller
     * @var IBoxController
     */
    protected $controller;

    /**
     * box content grouped by language id
     * @var BoxContent[]
     */
    public $boxContents;

    /**
     * @var IMenuPageHandler
     */
    protected $linkPageHandler;

    /**
     * page object
     * @var Page
     */
    protected $linkPage;

    /**
     * virtual show order of this box
     * @var int
     */
    public $virtualShowOrder = -1;

    /**
     * list of positions that support the edit button
     * @var string[]
     */
    public $editButtonPositions = [
        'headerBoxes',
        'sidebarLeft',
        'contentTop',
        'sidebarRight',
        'contentBottom',
        'footerBoxes',
        'footer',
    ];

    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        $value = parent::__get($name);

        if ($value === null && isset($this->data['additionalData'][$name])) {
            $value = $this->data['additionalData'][$name];
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    protected function handleData($data)
    {
        parent::handleData($data);

        // handle condition data
        if (isset($data['additionalData'])) {
            $this->data['additionalData'] = @\unserialize($data['additionalData'] ?: '');

            if (!\is_array($this->data['additionalData'])) {
                $this->data['additionalData'] = [];
            }
        } else {
            $this->data['additionalData'] = [];
        }
    }

    /**
     * Returns true if the active user can delete this box.
     *
     * @return  bool
     */
    public function canDelete()
    {
        if (WCF::getSession()->getPermission('admin.content.cms.canManageBox') && !$this->originIsSystem) {
            return true;
        }

        return false;
    }

    /**
     * Returns the box's content.
     *
     * @return  BoxContent[]
     */
    public function getBoxContents()
    {
        if ($this->boxContents === null) {
            $this->boxContents = [];

            $sql = "SELECT  *
                    FROM    wcf1_box_content
                    WHERE   boxID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->boxID]);
            while ($row = $statement->fetchArray()) {
                $this->boxContents[$row['languageID'] ?: 0] = new BoxContent(null, $row);
            }
        }

        return $this->boxContents;
    }

    /**
     * Sets the box's content.
     *
     * @param BoxContent[] $boxContents
     */
    public function setBoxContents($boxContents)
    {
        $this->boxContents = $boxContents;
    }

    /**
     * Returns the title of the box as set in the box content database table.
     *
     * @return  string
     */
    public function getBoxContentTitle()
    {
        $this->getBoxContents();
        if ($this->isMultilingual || $this->boxType === 'system') {
            if ($this->boxType === 'system' && $this->getController()->getTitle()) {
                return $this->getController()->getTitle();
            }

            if (isset($this->boxContents[WCF::getLanguage()->languageID])) {
                return $this->boxContents[WCF::getLanguage()->languageID]->title;
            }
        } elseif (isset($this->boxContents[0])) {
            return $this->boxContents[0]->title;
        }

        return '';
    }

    /**
     * Returns the title for the rendered version of this box.
     */
    public function getTitle(): string
    {
        if ($this->boxType == 'menu') {
            return $this->getMenu()->getTitle();
        }

        return $this->getBoxContentTitle();
    }

    /**
     * Returns the content for the rendered version of this box.
     *
     * @return  string
     */
    public function getContent()
    {
        if ($this->boxType == 'system') {
            return $this->getController()->getContent();
        } elseif ($this->boxType == 'menu') {
            return $this->getMenu()->getContent();
        }

        $this->getBoxContents();
        $boxContent = null;
        if ($this->isMultilingual) {
            if (isset($this->boxContents[WCF::getLanguage()->languageID])) {
                $boxContent = $this->boxContents[WCF::getLanguage()->languageID];
            }
        } else {
            if (isset($this->boxContents[0])) {
                $boxContent = $this->boxContents[0];
            }
        }

        if ($boxContent !== null) {
            if ($this->boxType == 'text') {
                return $boxContent->getFormattedContent();
            } elseif ($this->boxType == 'html') {
                return $boxContent->getParsedContent();
            } elseif ($this->boxType == 'tpl') {
                return $boxContent->getParsedTemplate($this->getTplName(WCF::getLanguage()->languageID));
            }
        }

        return '';
    }

    /**
     * Returns the rendered version of this box.
     *
     * @return  string
     */
    public function render()
    {
        if (!$this->hasContent()) {
            return '';
        }

        WCF::getTPL()->assign([
            'box' => $this,
        ]);

        return WCF::getTPL()->fetch('__box');
    }

    /**
     * Returns false if this box has no content.
     *
     * @return  bool
     */
    public function hasContent()
    {
        if ($this->boxType == 'system') {
            return $this->getController()->hasContent();
        } elseif ($this->boxType == 'menu') {
            return $this->getMenu()->hasContent();
        }

        $this->getBoxContents();
        $content = '';
        if ($this->isMultilingual) {
            if (isset($this->boxContents[WCF::getLanguage()->languageID])) {
                $content = $this->boxContents[WCF::getLanguage()->languageID]->content;
            }
        } else {
            if (isset($this->boxContents[0])) {
                $content = $this->boxContents[0]->content;
            }
        }

        return !empty($content);
    }

    /**
     * Returns the box controller.
     *
     * @return  IBoxController
     */
    public function getController()
    {
        if ($this->controller === null && $this->objectTypeID) {
            $className = ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID)->className;

            $this->controller = new $className();
            $this->controller->setBox($this);
        }

        return $this->controller;
    }

    /**
     * Returns the menu shown in the box.
     *
     * @return  Menu
     */
    public function getMenu()
    {
        if ($this->menu === null) {
            $this->menu = MenuCache::getInstance()->getMenuByID($this->menuID);
        }

        return $this->menu;
    }

    /**
     * Returns the image of this box or `null` if the box has no image.
     *
     * @return  ViewableMedia|null
     */
    public function getImage()
    {
        if ($this->boxType === 'menu') {
            return null;
        }

        if ($this->image === null) {
            if ($this->boxType === 'system') {
                $this->image = $this->getController()->getImage();
            } else {
                $this->getBoxContents();
                if ($this->isMultilingual) {
                    if (isset($this->boxContents[WCF::getLanguage()->languageID]) && $this->boxContents[WCF::getLanguage()->languageID]->imageID) {
                        $this->image = $this->boxContents[WCF::getLanguage()->languageID]->getImage();
                    }
                } elseif (isset($this->boxContents[0]) && $this->boxContents[0]->imageID) {
                    $this->image = $this->boxContents[0]->getImage();
                }
            }
        }

        if ($this->image === null || !$this->image->isAccessible()) {
            return null;
        }

        return $this->image;
    }

    /**
     * Returns the URL of this box.
     */
    public function getLink(): string
    {
        if ($this->boxType == 'system') {
            return $this->getController()->getLink();
        } elseif ($this->boxType == 'menu') {
            return '';
        }

        if ($this->linkPageObjectID) {
            $handler = $this->getLinkPageHandler();
            if ($handler && $handler instanceof ILookupPageHandler) {
                return $handler->getLink($this->linkPageObjectID);
            }
        }

        if ($this->linkPageID) {
            return $this->getLinkPage()->getLink();
        } else {
            return $this->externalURL;
        }
    }

    /**
     * Returns true if this box has a link.
     *
     * @return  bool
     */
    public function hasLink()
    {
        if ($this->boxType == 'system') {
            return $this->getController()->hasLink();
        } elseif ($this->boxType == 'menu') {
            return false;
        }

        return $this->linkPageID || !empty($this->externalURL);
    }

    /**
     * Returns the IMenuPageHandler of the linked page.
     *
     * @return  IMenuPageHandler|null
     * @throws  ImplementationException
     */
    protected function getLinkPageHandler()
    {
        $page = $this->getLinkPage();
        if ($page !== null && $page->handler) {
            if ($this->linkPageHandler === null) {
                $className = $page->handler;
                $this->linkPageHandler = new $className();
                if (!($this->linkPageHandler instanceof IMenuPageHandler)) {
                    throw new ImplementationException(\get_class($this->linkPageHandler), IMenuPageHandler::class);
                }
            }
        }

        return $this->linkPageHandler;
    }

    /**
     * Returns the page that is linked by this box.
     *
     * @return  Page|null
     */
    public function getLinkPage()
    {
        if ($this->linkPage === null && $this->linkPageID) {
            $this->linkPage = PageCache::getInstance()->getPage($this->linkPageID);
        }

        return $this->linkPage;
    }

    /**
     * Returns the template name of this box.
     *
     * @param int $languageID
     * @return  string
     */
    public function getTplName($languageID = null)
    {
        if ($this->boxType == 'tpl') {
            if ($this->isMultilingual) {
                return '__cms_box_' . $this->boxID . '_' . $languageID;
            }

            return '__cms_box_' . $this->boxID;
        }

        return '';
    }

    /**
     * Returns box to page assignments.
     *
     * @return  int[]
     */
    public function getPageIDs()
    {
        if ($this->pageIDs === null) {
            $sql = "SELECT  pageID
                    FROM    wcf1_box_to_page
                    WHERE   boxID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->boxID]);

            $this->pageIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
        }

        return $this->pageIDs;
    }

    /**
     * Returns the conditions for the box controller.
     *
     * @return  Condition[]
     * @deprecated 5.5 - use self::getControllerConditions() instead
     */
    public function getConditions()
    {
        return $this->getControllerConditions();
    }

    /**
     * Returns the conditions for the box controller.
     * The conditions are intended for the contents of the box.
     *
     * @return  Condition[]
     * @since   5.5
     */
    public function getControllerConditions(): array
    {
        /** @noinspection PhpUndefinedMethodInspection */
        if ($this->boxType === 'system' && $this->getController() instanceof IConditionBoxController && $this->getController()->getConditionDefinition()) {
            /** @noinspection PhpUndefinedMethodInspection */
            return ConditionHandler::getInstance()->getConditions(
                $this->getController()->getConditionDefinition(),
                $this->boxID
            );
        }

        return [];
    }

    /**
     * Returns the conditions for the visibility of the box.
     *
     * @return  Condition[]
     * @since   5.5
     */
    public function getVisibilityConditions(): array
    {
        return ConditionHandler::getInstance()->getConditions(
            self::VISIBILITY_CONDITIONS_OBJECT_TYPE_NAME,
            $this->boxID
        );
    }

    public function isVisible(): bool
    {
        $conditions = $this->getVisibilityConditions();
        foreach ($conditions as $condition) {
            if (!$condition->getObjectType()->getProcessor()->showContent($condition)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if this box is accessible by current user.
     *
     * @return  bool
     */
    public function isAccessible()
    {
        $canAccess = SimpleAclResolver::getInstance()->canAccess('com.woltlab.wcf.box', $this->boxID);

        if ($this->invertPermissions) {
            $canAccess = !$canAccess;
        }

        return $canAccess;
    }

    /**
     * Sets the virtual show order of this box.
     *
     * @param int $virtualShowOrder
     */
    public function setVirtualShowOrder($virtualShowOrder)
    {
        $this->virtualShowOrder = $virtualShowOrder;
    }

    /**
     * Returns true if an edit button should be displayed for this box.
     *
     * @return      bool
     * @since       5.2
     */
    public function showEditButton()
    {
        if (
            WCF::getSession()->getPermission('admin.content.cms.canManageBox')
            && $this->boxType !== 'menu'
            && \in_array($this->position, $this->editButtonPositions)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns the box with the given identifier.
     *
     * @param string $identifier
     * @return  Box
     */
    public static function getBoxByIdentifier($identifier)
    {
        $sql = "SELECT  *
                FROM    wcf1_box
                WHERE   identifier = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$identifier]);

        return $statement->fetchObject(self::class);
    }

    /**
     * Returns the box with the given name.
     *
     * @param string $name
     * @return  Box
     */
    public static function getBoxByName($name)
    {
        $sql = "SELECT  *
                FROM    wcf1_box
                WHERE   name = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$name]);

        return $statement->fetchObject(self::class);
    }

    /**
     * Returns the box with the menu id.
     *
     * @param int $menuID
     * @return  Box
     */
    public static function getBoxByMenuID($menuID)
    {
        $sql = "SELECT  *
                FROM    wcf1_box
                WHERE   menuID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$menuID]);

        return $statement->fetchObject(self::class);
    }
}
