<?php

namespace wcf\acp\page;

use wcf\data\devtools\project\DevtoolsProject;
use wcf\http\Helper;
use wcf\page\AbstractPage;
use wcf\system\WCF;

/**
 * Shows the devtools project sync form.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class DevtoolsProjectSyncPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.devtools.project.list';

    /**
     * @inheritDoc
     */
    public $neededModules = ['ENABLE_DEVELOPER_TOOLS'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.configuration.package.canInstallPackage'];

    /**
     * devtools project
     * @var ?DevtoolsProject
     */
    public $object;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->object = Helper::fetchObjectFromQueryParameter(DevtoolsProject::class);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'objectID' => $this->object->projectID,
            'object' => $this->object,
            'project' => $this->object,
        ]);
    }
}
