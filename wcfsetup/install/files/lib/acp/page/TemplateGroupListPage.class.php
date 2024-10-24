<?php

namespace wcf\acp\page;

use wcf\data\template\group\TemplateGroupList;
use wcf\page\SortablePage;

/**
 * Shows a list of installed template groups.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    TemplateGroupList $objectList
 */
class TemplateGroupListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.template.group.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.template.canManageTemplate'];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'templateGroupName';

    /**
     * @inheritDoc
     */
    public $objectListClassName = TemplateGroupList::class;

    /**
     * @inheritDoc
     */
    public $validSortFields = [
        'templateGroupID',
        'templateGroupName',
        'templateGroupFolderName',
        'templates',
        'styles',
    ];

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlSelects = "
            (
                SELECT  COUNT(*)
                FROM    wcf1_template
                WHERE   templateGroupID = template_group.templateGroupID
            ) AS templates,
            (
                SELECT  COUNT(*)
                FROM    wcf1_style
                WHERE   templateGroupID = template_group.templateGroupID
            ) AS styles";
    }
}
