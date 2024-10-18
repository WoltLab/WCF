<?php

namespace wcf\data\contact\recipient;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes contact recipient related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @method  ContactRecipientEditor[]    getObjects()
 * @method  ContactRecipientEditor      getSingleObject()
 */
class ContactRecipientAction extends AbstractDatabaseObjectAction implements ISortableAction, IToggleAction
{
    use TDatabaseObjectToggle;

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
    protected $requireACP = ['create', 'delete', 'toggle', 'update', 'updatePosition'];

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

    /**
     * @inheritDoc
     */
    public function validateUpdatePosition()
    {
        WCF::getSession()->checkPermissions($this->permissionsUpdate);

        if (!isset($this->parameters['data']['structure']) || !\is_array($this->parameters['data']['structure'])) {
            throw new UserInputException('structure');
        }

        $recipientList = new ContactRecipientList();
        $recipientList->setObjectIDs($this->parameters['data']['structure'][0]);
        if ($recipientList->countObjects() != \count($this->parameters['data']['structure'][0])) {
            throw new UserInputException('structure');
        }

        $this->readInteger('offset', true, 'data');
    }

    /**
     * @inheritDoc
     */
    public function updatePosition()
    {
        $sql = "UPDATE  wcf1_contact_recipient
                SET     showOrder = ?
                WHERE   recipientID = ?";
        $statement = WCF::getDB()->prepare($sql);

        $showOrder = $this->parameters['data']['offset'];
        WCF::getDB()->beginTransaction();
        foreach ($this->parameters['data']['structure'][0] as $recipientID) {
            $statement->execute([
                $showOrder++,
                $recipientID,
            ]);
        }
        WCF::getDB()->commitTransaction();
    }
}
