<?php

namespace wcf\data\bbcode\media\provider;

use wcf\command\bbcode\media\provider\DisableBBCodeMediaProvider;
use wcf\command\bbcode\media\provider\EnableBBCodeMediaProvider;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;

/**
 * Executes BBCode media provider-related actions.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<BBCodeMediaProvider, BBCodeMediaProviderEditor>
 */
class BBCodeMediaProviderAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    /**
     * @inheritDoc
     */
    protected $className = BBCodeMediaProviderEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.content.bbcode.canManageBBCode'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.content.bbcode.canManageBBCode'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['delete', 'update'];

    /**
     * @deprecated 6.3
     */
    public function validateToggle()
    {
        $this->validateUpdate();
    }

    /**
     * @deprecated 6.3 use the `EnableBBCodeMediaProvider` or `DisableBBCodeMediaProvider` commands instead.
     */
    public function toggle()
    {
        foreach ($this->objects as $editor) {
            if ($editor->isDisabled) {
                (new EnableBBCodeMediaProvider($editor->getDecoratedObject()))();
            } else {
                (new DisableBBCodeMediaProvider($editor->getDecoratedObject()))();
            }
        }
    }
}
