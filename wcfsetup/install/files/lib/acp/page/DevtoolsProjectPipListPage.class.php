<?php

namespace wcf\acp\page;

use wcf\data\devtools\project\DevtoolsProject;
use wcf\http\Helper;
use wcf\page\AbstractPage;
use wcf\system\WCF;

/**
 * Shows the pip data of a project.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class DevtoolsProjectPipListPage extends AbstractPage
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
    public $project;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->project = Helper::fetchObjectFromQueryParameter(DevtoolsProject::class);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'project' => $this->project,
        ]);
    }
}
