<?php

/**
 * Insert the shared template group.
 *
 * @author Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\template\group\TemplateGroupAction;

(new TemplateGroupAction([], 'create', [
    'data' => [
        'templateGroupName' => 'wcf.acp.template.group.shared',
        'templateGroupFolderName' => '_wcf_shared/',
        'parentTemplateGroupID' => null,
    ]
]))->executeAction();
