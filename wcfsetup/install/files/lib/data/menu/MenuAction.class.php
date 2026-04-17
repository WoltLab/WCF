<?php

namespace wcf\data\menu;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\box\BoxAction;
use wcf\data\box\BoxEditor;
use wcf\data\DatabaseObject;
use wcf\data\TI18nDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;

/**
 * Executes menu related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<Menu, MenuEditor>
 */
class MenuAction extends AbstractDatabaseObjectAction
{
    use TI18nDatabaseObjectAction;

    /**
     * @inheritDoc
     */
    protected $className = MenuEditor::class;

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
    protected $requireACP = ['create', 'delete', 'update'];

    #[\Override]
    public function create()
    {
        // `title` column doesn't have a default value
        $this->parameters['data']['title'] = $this->parameters['data']['title'] ?? '';

        /** @var Menu $menu */
        $menu = parent::create();

        $this->saveI18nValue($menu);

        // create box
        $boxData = $this->parameters['boxData'];
        $boxData['menuID'] = $menu->menuID;
        $boxData['identifier'] = '';
        $boxAction = new BoxAction([], 'create', [
            'data' => $boxData,
            'pageIDs' => $this->parameters['pageIDs'] ?? [],
            'acl' => $this->parameters['acl'] ?? []
        ]);
        $returnValues = $boxAction->executeAction();

        // set generic box identifier
        $boxEditor = new BoxEditor($returnValues['returnValues']);
        $boxEditor->update([
            'identifier' => 'com.woltlab.wcf.genericMenuBox' . $boxEditor->boxID,
        ]);

        // return new menu
        return $menu;
    }

    #[\Override]
    public function update()
    {
        parent::update();

        foreach ($this->getObjects() as $menu) {
            $this->saveI18nValue($menu->getDecoratedObject());
        }
    }

    #[\Override]
    public function validateDelete()
    {
        parent::validateDelete();

        foreach ($this->getObjects() as $object) {
            if (!$object->canDelete()) {
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
            'title' => 'wcf.menu.\w+'
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
        \assert($object instanceof Menu);

        return \str_replace(
            '\w+',
            $object->identifier ?: 'com.woltlab.wcf.genericMenu' . $object->menuID,
            $regex
        );
    }
}
