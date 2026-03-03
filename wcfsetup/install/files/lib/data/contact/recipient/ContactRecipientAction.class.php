<?php

namespace wcf\data\contact\recipient;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;
use wcf\data\TI18nDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;

/**
 * Executes contact recipient related actions.
 *
 * @author  Olaf Braun, Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @extends AbstractDatabaseObjectAction<ContactRecipient, ContactRecipientEditor>
 */
class ContactRecipientAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    use TDatabaseObjectToggle;
    use TI18nDatabaseObjectAction;

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

    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
        parent::validateDelete();

        foreach ($this->getObjects() as $object) {
            if ($object->originIsSystem) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validateToggle()
    {
        parent::validateUpdate();
    }

    #[\Override]
    public function create()
    {
        // Database columns do not have default values
        if (!isset($this->parameters['data']['name'])) {
            $this->parameters['data']['name'] = '';
        }
        if (!isset($this->parameters['data']['email'])) {
            $this->parameters['data']['email'] = '';
        }

        $contactRecipient = parent::create();

        $this->saveI18nValue($contactRecipient);

        return $contactRecipient;
    }

    #[\Override]
    public function delete()
    {
        $count = parent::delete();

        $this->deleteI18nValues();

        return $count;
    }

    #[\Override]
    public function update()
    {
        parent::update();

        foreach ($this->getObjects() as $contactRecipient) {
            $this->saveI18nValue($contactRecipient->getDecoratedObject());
        }
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function getI18nSaveTypes(): array
    {
        return [
            'name' => 'wcf.contact.recipient.name\d+',
            'email' => 'wcf.contact.recipient.email\d+',
        ];
    }

    #[\Override]
    public function getLanguageCategory(): string
    {
        return 'wcf.contact';
    }

    #[\Override]
    public function getPackageID(): int
    {
        return 1;
    }
}
