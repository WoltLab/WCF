<?php

namespace wcf\acp\page;

use wcf\data\acp\session\log\ACPSessionLog;
use wcf\page\AbstractGridViewPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\gridView\admin\ACPSessionGridView;
use wcf\system\WCF;

/**
 * Shows the details of a logged sessions.
 *
 * @author      Olaf Braun, Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractGridViewPage<ACPSessionGridView>
 */
final class ACPSessionLogPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.log.session';

    /**
     * @inheritDoc
     */
    public $templateName = 'acpSessionLog';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canViewLog'];

    public ACPSessionLog $sessionLog;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        // get session log
        if (!isset($_REQUEST['id'])) {
            throw new IllegalLinkException();
        }
        $this->sessionLog = new ACPSessionLog(\intval($_REQUEST['id']));
        if (!$this->sessionLog->sessionLogID) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'sessionLog' => $this->sessionLog,
        ]);
    }

    #[\Override]
    protected function createGridView(): ACPSessionGridView
    {
        return new ACPSessionGridView($this->sessionLog->sessionLogID);
    }

    #[\Override]
    protected function getBaseUrlParameters(): array
    {
        return ['id' => $this->sessionLog->sessionLogID];
    }
}
