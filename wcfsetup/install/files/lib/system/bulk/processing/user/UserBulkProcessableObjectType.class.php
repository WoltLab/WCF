<?php

namespace wcf\system\bulk\processing\user;

use wcf\system\bulk\processing\AbstractBulkProcessableObjectType;

/**
 * Bulk processable object type implementation for users.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class UserBulkProcessableObjectType extends AbstractBulkProcessableObjectType
{
    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_userConditions';
}
