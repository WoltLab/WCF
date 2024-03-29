<?php

namespace wcf\system\cache\builder;

use wcf\data\contact\option\ContactOptionList;

/**
 * Caches contact options.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ContactOptionCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        $list = new ContactOptionList();
        $list->sqlSelects = "CONCAT('contactOption', CAST(contact_option.optionID AS CHAR)) AS optionName";
        $list->readObjects();

        return $list->getObjects();
    }
}
