<?php

namespace wcf\data\acp\session\log;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit ACP session logs.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.3 Use `ACPSessionLogBuilder` instead.
 *
 * @mixin       ACPSessionLog
 * @extends DatabaseObjectEditor<ACPSessionLog>
 */
class ACPSessionLogEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ACPSessionLog::class;
}
