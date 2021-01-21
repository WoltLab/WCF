<?php

namespace wcf\acp\form;

use wcf\data\bbcode\media\provider\BBCodeMediaProvider;
use wcf\data\bbcode\media\provider\BBCodeMediaProviderAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the BBCode media provider edit form.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Form
 */
class BBCodeMediaProviderEditForm extends BBCodeMediaProviderAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.bbcode.mediaProvider.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.bbcode.canManageBBCode'];

    /**
     * id of the edited media provider
     * @var int
     */
    public $providerID = 0;

    /**
     * edited media provider object
     * @var BBCodeMediaProvider
     */
    public $mediaProvider;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->providerID = \intval($_REQUEST['id']);
        }
        $this->mediaProvider = new BBCodeMediaProvider($this->providerID);
        if (!$this->mediaProvider->providerID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        // update media-provider
        $this->objectAction = new BBCodeMediaProviderAction([$this->providerID], 'update', ['data' => \array_merge($this->additionalFields, [
            'title' => $this->title,
            'regex' => $this->regex,
            'html' => $this->html,
            'className' => $this->className,
        ])]);
        $this->objectAction->executeAction();

        $this->saved();

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (empty($_POST)) {
            $this->title = $this->mediaProvider->title;
            $this->regex = $this->mediaProvider->regex;
            $this->html = $this->mediaProvider->html;
            $this->className = $this->mediaProvider->className;
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'mediaProvider' => $this->mediaProvider,
            'action' => 'edit',
        ]);
    }
}
