<?php

namespace wcf\acp\form;

/**
 * Shows the user bulk processing form.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserBulkProcessingForm extends AbstractBulkProcessingForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.bulkProcessing';

    /**
     * @inheritDoc
     */
    public $objectTypeName = 'com.woltlab.wcf.bulkProcessing.user';
}
