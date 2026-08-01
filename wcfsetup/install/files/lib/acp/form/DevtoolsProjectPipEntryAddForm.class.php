<?php

namespace wcf\acp\form;

use wcf\data\devtools\project\DevtoolsProject;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\http\Helper;
use wcf\system\devtools\pip\DevtoolsPip;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the form to add a new entry for a specific pip and project.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 *
 * @extends AbstractFormBuilderForm<null>
 */
class DevtoolsProjectPipEntryAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.devtools.project.list';

    /**
     * type of the added/edited pip entry
     * @var string
     */
    public $entryType;

    /**
     * @inheritDoc
     */
    public $neededModules = ['ENABLE_DEVELOPER_TOOLS'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.configuration.package.canInstallPackage'];

    /**
     * name of the requested pip
     * @var string
     */
    public $pip = '';

    /**
     * devtools project
     * @var DevtoolsProject
     */
    public $project;

    /**
     * devtools pip object for the requested pip
     * @var DevtoolsPip
     */
    public $pipObject;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->project = Helper::fetchObjectFromQueryParameter(DevtoolsProject::class);

        if ($this->project->validatePackageXml() !== '') {
            throw new IllegalLinkException();
        }

        if (isset($_REQUEST['pip'])) {
            $this->pip = StringUtil::trim($_REQUEST['pip']);
        }

        $filteredPips = \array_filter($this->project->getPips(), function (DevtoolsPip $pip) {
            return $pip->pluginName === $this->pip;
        });
        if (\count($filteredPips) === 1) {
            $this->pipObject = \reset($filteredPips);
        } else {
            throw new IllegalLinkException();
        }

        if (!$this->pipObject->supportsGui()) {
            throw new IllegalLinkException();
        }

        $pip = $this->pipObject->getPip();
        \assert($pip instanceof IGuiPackageInstallationPlugin);
        if (isset($_REQUEST['entryType'])) {
            $this->entryType = StringUtil::trim($_REQUEST['entryType']);

            try {
                $pip->setEntryType($this->entryType);
            } catch (\InvalidArgumentException $e) {
                throw new IllegalLinkException();
            }
        } elseif (!empty($pip->getEntryTypes())) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    public function createForm()
    {
        parent::createForm();

        $this->addPipFormFields();
    }

    /**
     * Adds the pip-specific form fields.
     *
     * @return void
     */
    protected function addPipFormFields()
    {
        $this->form->appendChild(
            FormContainer::create('data')
                ->label('wcf.global.form.data')
        );

        $pip = $this->pipObject->getPip();
        \assert($pip instanceof IGuiPackageInstallationPlugin);
        $pip->populateForm($this->form);

        EventHandler::getInstance()->fireAction($this, 'addPipFormFields');
    }

    #[\Override]
    public function save()
    {
        AbstractForm::save();

        $pip = $this->pipObject->getPip();
        \assert($pip instanceof IGuiPackageInstallationPlugin);
        $pip->addEntry($this->form);

        $this->saved();

        WCF::getTPL()->assign('success', true);
    }

    #[\Override]
    public function setFormAction()
    {
        $this->form->action(LinkHandler::getInstance()->getControllerLink(DevtoolsProjectPipEntryAddForm::class, [
            'entryType' => $this->entryType,
            'id' => $this->project->projectID,
            'pip' => $this->pip,
        ]));
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'action' => 'add',
            'entryType' => $this->entryType,
            'pip' => $this->pip,
            'pipObject' => $this->pipObject,
            'project' => $this->project,
        ]);
    }
}
