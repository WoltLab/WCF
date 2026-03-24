<?php

namespace wcf\acp\form;

use wcf\form\AbstractForm;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\IFormDocument;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the form to edit an exiting entry for a specific pip and project.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class DevtoolsProjectPipEntryEditForm extends DevtoolsProjectPipEntryAddForm
{
    /**
     * identifier of the edited pip entry
     * @var string
     */
    public $identifier = '';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['identifier'])) {
            $this->identifier = StringUtil::trim($_REQUEST['identifier']);
        }
    }

    #[\Override]
    public function readData()
    {
        $pip = $this->pipObject->getPip();
        \assert($pip instanceof IGuiPackageInstallationPlugin);

        if (!empty($_POST)) {
            $pip->setEditedEntryIdentifier($this->identifier);
        }

        parent::readData();

        if (empty($_POST)) {
            if (!$pip->setEntryData($this->identifier, $this->form)) {
                throw new IllegalLinkException();
            }
        }
    }

    #[\Override]
    protected function addPipFormFields()
    {
        $this->form->formMode(IFormDocument::FORM_MODE_UPDATE);

        parent::addPipFormFields();
    }

    #[\Override]
    public function setFormAction()
    {
        $this->form->action(LinkHandler::getInstance()->getLink('DevtoolsProjectPipEntryEdit', [
            'entryType' => $this->entryType,
            'id' => $this->project->projectID,
            'pip' => $this->pip,
            'identifier' => $this->identifier,
        ]));
    }

    #[\Override]
    public function save()
    {
        AbstractForm::save();

        $pip = $this->pipObject->getPip();
        \assert($pip instanceof IGuiPackageInstallationPlugin);
        $newIdentifier = $pip->editEntry($this->form, $this->identifier);

        $this->saved();

        if ($this->identifier !== $newIdentifier) {
            // reload the page with the new identifier and store success
            // message in session variables
            WCF::getSession()->register($this->project->projectID . '-' . $this->pip . '-success', 1);

            HeaderUtil::redirect(LinkHandler::getInstance()->getLink('DevtoolsProjectPipEntryEdit', [
                'entryType' => $this->entryType,
                'id' => $this->project->projectID,
                'pip' => $this->pip,
                'identifier' => $newIdentifier,
            ]));

            exit;
        } else {
            WCF::getTPL()->assign('success', true);
        }
    }

    #[\Override]
    public function saved()
    {
        AbstractForm::saved();

        $this->form->showSuccessMessage(true);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        // check if a success message has been stored in session variables
        // from previous request
        if (WCF::getSession()->getVar($this->project->projectID . '-' . $this->pip . '-success') == 1) {
            WCF::getSession()->unregister($this->project->projectID . '-' . $this->pip . '-success');

            WCF::getTPL()->assign('success', true);
        }

        WCF::getTPL()->assign([
            'action' => 'edit',
        ]);
    }
}
