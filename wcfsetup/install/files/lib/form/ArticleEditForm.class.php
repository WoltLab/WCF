<?php

namespace wcf\form;

use wcf\system\WCF;

/**
 * Shows the article edit form.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
class ArticleEditForm extends \wcf\acp\form\ArticleEditForm
{
    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign(['articleIsFrontend' => true]);
    }
}
