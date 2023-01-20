<?php

namespace wcf\system\cli\command;

/**
 * Exits WCF.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ExitCLICommand implements ICLICommand
{
    /**
     * @inheritDoc
     */
    public function execute(array $parameters)
    {
        exit;
    }

    /**
     * @inheritDoc
     */
    public function canAccess()
    {
        // everyone may access this command
        return true;
    }
}
