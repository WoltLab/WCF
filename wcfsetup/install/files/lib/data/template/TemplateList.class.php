<?php

namespace wcf\data\template;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of templates.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends DatabaseObjectList<Template>
 */
class TemplateList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Template::class;

    public function __construct()
    {
        parent::__construct();

        $this->sqlSelects = 'package.package, template_group.templateGroupFolderName';
        $this->sqlJoins = "
            LEFT JOIN   wcf1_package package
            ON          package.packageID = template.packageID
            LEFT JOIN   wcf1_template_group template_group
            ON          template_group.templateGroupID = template.templateGroupID";
    }
}
