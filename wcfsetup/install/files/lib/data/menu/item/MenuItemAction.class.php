<?php

namespace wcf\data\menu\item;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\DatabaseObject;
use wcf\data\ISortableAction;
use wcf\data\IToggleAction;
use wcf\data\menu\Menu;
use wcf\data\TDatabaseObjectToggle;
use wcf\data\TI18nDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes menu item related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @extends AbstractDatabaseObjectAction<MenuItem, MenuItemEditor>
 */
class MenuItemAction extends AbstractDatabaseObjectAction implements ISortableAction, IToggleAction
{
    use TDatabaseObjectToggle;
    use TI18nDatabaseObjectAction;

    /**
     * @inheritDoc
     */
    protected $className = MenuItemEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.content.cms.canManageMenu'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.content.cms.canManageMenu'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.content.cms.canManageMenu'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'toggle', 'update'];

    #[\Override]
    public function create()
    {
        // `title` column doesn't have a default value
        $this->parameters['data']['title'] = $this->parameters['data']['title'] ?? '';

        /** @var MenuItem $menuItem */
        $menuItem = parent::create();

        if (!$menuItem->identifier) {
            $editor = new MenuItemEditor($menuItem);
            $editor->update([
                'identifier' => 'com.woltlab.wcf.generic' . $menuItem->itemID,
            ]);
            $menuItem = new MenuItem($menuItem->itemID);
        }

        $this->saveI18nValue($menuItem);

        return $menuItem;
    }

    #[\Override]
    public function update()
    {
        parent::update();

        foreach ($this->getObjects() as $editor) {
            $this->saveI18nValue($editor->getDecoratedObject());
        }
    }

    #[\Override]
    public function validateToggle()
    {
        parent::validateUpdate();

        foreach ($this->getObjects() as $object) {
            if (!$object->canDisable()) {
                throw new PermissionDeniedException();
            }
        }
    }

    #[\Override]
    public function validateUpdatePosition()
    {
        WCF::getSession()->checkPermissions(['admin.content.cms.canManageMenu']);

        // validate menu id
        $this->readInteger('menuID');
        $menu = new Menu($this->parameters['menuID']);
        if (!$menu->menuID) {
            throw new UserInputException('menuID');
        }

        // validate structure
        if (
            !isset($this->parameters['data'])
            || !isset($this->parameters['data']['structure'])
            || !\is_array($this->parameters['data']['structure'])
        ) {
            throw new UserInputException('structure');
        }

        $menuItemIDs = [];
        foreach ($this->parameters['data']['structure'] as $menuItems) {
            $menuItemIDs = \array_merge($menuItemIDs, $menuItems);
        }

        $menuItemList = new MenuItemList();
        $menuItemList->getConditionBuilder()->add('menu_item.itemID IN (?)', [$menuItemIDs]);
        $menuItemList->getConditionBuilder()->add('menu_item.menuID = ?', [$this->parameters['menuID']]);
        $menuItemList->readObjects();
        $menuItems = $menuItemList->getObjects();

        if (\count($menuItems) != \count($menuItemIDs)) {
            throw new UserInputException('structure');
        }

        foreach ($this->parameters['data']['structure'] as $parentItemID => $children) {
            if ($parentItemID && !isset($menuItems[$parentItemID])) {
                throw new UserInputException('structure');
            }
        }
    }

    #[\Override]
    public function updatePosition()
    {
        $sql = "UPDATE  wcf1_menu_item
                SET     parentItemID = ?,
                        showOrder = ?
                WHERE   itemID = ?";
        $statement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        foreach ($this->parameters['data']['structure'] as $parentItemID => $children) {
            foreach ($children as $showOrder => $menuItemID) {
                $statement->execute([
                    $parentItemID ?: null,
                    $showOrder + 1,
                    $menuItemID,
                ]);
            }
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function getI18nSaveTypes(): array
    {
        return [
            'title' => 'wcf.menu.item.\w+',
            'externalURL' => 'wcf.menu.item.externalURL\d+',
        ];
    }

    #[\Override]
    public function getLanguageCategory(): string
    {
        return 'wcf.menu';
    }

    #[\Override]
    public function getPackageID(): int
    {
        return PACKAGE_ID;
    }

    protected function getLanguageItem(DatabaseObject $object, string $regex): string
    {
        \assert($object instanceof MenuItem);
        if (\str_contains($regex, '\d+')) {
            return \str_replace('\d+', (string)$object->itemID, $regex);
        } else {
            return \str_replace(
                '\w+',
                $object->identifier ?: 'com.woltlab.wcf.generic' . $object->itemID,
                $regex
            );
        }
    }
}
