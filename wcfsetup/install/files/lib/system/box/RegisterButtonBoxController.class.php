<?php

namespace wcf\system\box;

use wcf\system\WCF;

/**
 * Box that shows the register button.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Box
 * @since   3.0
 */
class RegisterButtonBoxController extends AbstractBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = ['contentTop', 'contentBottom', 'sidebarLeft', 'sidebarRight'];

    /**
     * @inheritDoc
     */
    protected function loadContent()
    {
        if (!WCF::getUser()->userID && !REGISTER_DISABLED) {
            $this->content = WCF::getTPL()->fetch('boxRegisterButton', 'wcf', ['box' => $this->box], true);
        }
    }
}
