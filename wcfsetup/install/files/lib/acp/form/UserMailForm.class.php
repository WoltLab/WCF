<?php

namespace wcf\acp\form;

use wcf\data\user\group\UserGroup;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\HiddenFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\user\UserFormField;
use wcf\system\WCF;

/**
 * Shows the user mail form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserMailForm extends AbstractFormBuilderForm
{
    #[\Override]
    public function readParameters(): void
    {
        parent::readParameters();

        $this->activeMenuItem = match ($this->action) {
            'all' => 'wcf.acp.menu.link.user.mail',
            'group' => 'wcf.acp.menu.link.group.mail',
            default => 'wcf.acp.menu.link.user.list',
        };
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        if ($this->action == 'group') {
            $this->form->appendChild($this->getGroupFormField());
        }

        if ($this->action == '') {
            $this->form->appendChild($this->getUserFormField());
        }

        $this->form->appendChildren([
            TextFormField::create('subject')
                ->label('wcf.acp.user.sendMail.subject')
                ->required(),
            MultilineTextFormField::create('text')
                ->label('wcf.acp.user.sendMail.text')
                ->required(),
            BooleanFormField::create('enableHTML')
                ->label('wcf.acp.user.sendMail.enableHTML'),
            HiddenFormField::create('action')
                ->value($this->action),
        ]);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'action' => $this->action,
        ]);
    }

    #[\Override]
    public function save()
    {
        AbstractForm::save();

        $formData = $this->form->getData();
        $userMailData = WCF::getSession()->getVar('userMailData');
        if ($userMailData === null) {
            $userMailData = [];
        }
        $mailID = \count($userMailData);
        $userMailData[$mailID] = [
            'action' => $this->action,
            'userIDs' => $formData['userIDs'] ?? [],
            'groupIDs' => $formData['groupIDs'] ?? [],
            'subject' => $formData['data']['subject'],
            'text' => $formData['data']['text'],
            'enableHTML' => $formData['data']['enableHTML'],
            'from' => \MAIL_FROM_ADDRESS,
            'fromName' => \MAIL_FROM_NAME,
        ];
        WCF::getSession()->register('userMailData', $userMailData);
        $this->saved();

        WCF::getTPL()->assign('mailID', $mailID);
    }

    private function getGroupFormField(): MultipleSelectionFormField
    {
        return MultipleSelectionFormField::create('groupIDs')
            ->label('wcf.acp.user.sendMail.groups')
            ->required()
            ->options(
                UserGroup::getSortedAccessibleGroups([], [UserGroup::GUESTS, UserGroup::EVERYONE]),
                false,
                false
            );
    }

    private function getUserFormField(): UserFormField
    {
        $formField = UserFormField::create('userIDs')
            ->label('wcf.acp.user.sendMail.markedUsers')
            ->required()
            ->multiple();

        if (!count($_POST)) {
            if (!empty($_GET['id'])) {
                $formField->value([\intval($_GET['id'])]);
            } else {
                $objectTypeID = ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user');
                $users = ClipboardHandler::getInstance()->getMarkedItems($objectTypeID);
                if ($users === []) {
                    throw new IllegalLinkException();
                }
                $formField->value(\array_keys($users));
            }
        }

        return $formField;
    }
}
