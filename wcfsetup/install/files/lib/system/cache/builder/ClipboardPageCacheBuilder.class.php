<?php

namespace wcf\system\cache\builder;

use wcf\system\WCF;

/**
 * Caches clipboard pages.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ClipboardPageCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        $sql = "SELECT  pageClassName, actionID
                FROM    wcf1_clipboard_page";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        return $statement->fetchMap('pageClassName', 'actionID', false);
    }
}
