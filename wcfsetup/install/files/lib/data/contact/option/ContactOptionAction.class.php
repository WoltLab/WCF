<?php

namespace wcf\data\contact\option;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\data\TDatabaseObjectToggle;
use wcf\data\TI18nDatabaseObjectAction;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes contact option related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @extends AbstractDatabaseObjectAction<ContactOption, ContactOptionEditor>
 */
class ContactOptionAction extends AbstractDatabaseObjectAction implements ISortableAction
{
    use TI18nDatabaseObjectAction;
    use TDatabaseObjectToggle;

    /**
     * @inheritDoc
     */
    protected $className = ContactOptionEditor::class;

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
    protected $requireACP = ['create', 'delete', 'update', 'updatePosition', 'toggle'];

    /**
     * @inheritDoc
     */
    public function validateUpdatePosition()
    {
        WCF::getSession()->checkPermissions($this->permissionsUpdate);

        if (!isset($this->parameters['data']['structure']) || !\is_array($this->parameters['data']['structure'])) {
            throw new UserInputException('structure');
        }

        $recipientList = new ContactOptionList();
        $recipientList->setObjectIDs($this->parameters['data']['structure'][0]);
        if ($recipientList->countObjects() != \count($this->parameters['data']['structure'][0])) {
            throw new UserInputException('structure');
        }
    }

    /**
     * @inheritDoc
     */
    public function updatePosition()
    {
        $sql = "UPDATE  wcf1_contact_option
                SET     showOrder = ?
                WHERE   optionID = ?";
        $statement = WCF::getDB()->prepare($sql);

        $showOrder = 1;
        WCF::getDB()->beginTransaction();
        foreach ($this->parameters['data']['structure'][0] as $optionID) {
            $statement->execute([
                $showOrder++,
                $optionID,
            ]);
        }
        WCF::getDB()->commitTransaction();
    }

    #[\Override]
    public function create()
    {
        $option = parent::create();

        $this->saveI18nValue($option);

        return $option;
    }

    #[\Override]
    public function delete()
    {
        $result = parent::delete();

        $this->deleteI18nValues();

        return $result;
    }

    #[\Override]
    public function update()
    {
        parent::update();

        foreach ($this->objects as $editor) {
            $this->saveI18nValue($editor->getDecoratedObject());
        }
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function getI18nSaveTypes(): array
    {
        return [
            'optionTitle' => 'wcf.contact.option\d+',
            'optionDescription' => 'wcf.contact.optionDescription\d+',
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
