<?php

namespace wcf\data\menu\item;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\DatabaseObject;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;
use wcf\data\TI18nDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;

/**
 * Executes menu item related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<MenuItem, MenuItemEditor>
 */
class MenuItemAction extends AbstractDatabaseObjectAction implements IToggleAction
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
        return \PACKAGE_ID;
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
