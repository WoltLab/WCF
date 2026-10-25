<?php

namespace wcf\data\contact\recipient;

use wcf\command\contact\recipient\DeleteContactRecipient;
use wcf\command\contact\recipient\DisableContactRecipient;
use wcf\command\contact\recipient\EnableContactRecipient;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\exception\PermissionDeniedException;

/**
 * Executes contact recipient related actions.
 *
 * @author  Olaf Braun, Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<ContactRecipient, ContactRecipientEditor>
 * @deprecated 6.3 use `ContactRecipientBuilder` with the `CreateContactRecipient` / `UpdateContactRecipient` commands instead.
 */
class ContactRecipientAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    /**
     * @inheritDoc
     */
    protected $className = ContactRecipientEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.contact.canManageContactForm'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.contact.canManageContactForm'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.contact.canManageContactForm'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'toggle', 'update'];

    #[\Override]
    public function validateDelete()
    {
        parent::validateDelete();

        foreach ($this->getObjects() as $object) {
            if ($object->originIsSystem !== 0) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @deprecated 6.3
     */
    #[\Override]
    public function validateToggle()
    {
        parent::validateUpdate();
    }

    /**
     * @deprecated 6.3 use the `DeleteContactRecipient` command instead.
     */
    #[\Override]
    public function delete()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->objects as $object) {
            new DeleteContactRecipient($object->getDecoratedObject())();
        }

        return \count($this->objects);
    }

    /**
     * @deprecated 6.3 use the `EnableContactRecipient` or `DisableContactRecipient` commands instead.
     */
    #[\Override]
    public function toggle()
    {
        foreach ($this->objects as $editor) {
            if ($editor->isDisabled !== 0) {
                new EnableContactRecipient($editor->getDecoratedObject())();
            } else {
                new DisableContactRecipient($editor->getDecoratedObject())();
            }
        }
    }
}
