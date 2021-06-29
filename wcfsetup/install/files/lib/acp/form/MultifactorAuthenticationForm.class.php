<?php

namespace wcf\acp\form;

use wcf\system\WCF;

/**
 * Represents the multi-factor authentication form.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Form
 * @since   5.4
 */
class MultifactorAuthenticationForm extends \wcf\form\MultifactorAuthenticationForm
{
    /**
     * @inheritDoc
     */
    public function saved()
    {
        WCF::getSession()->registerReauthentication();

        parent::saved();
    }
}
