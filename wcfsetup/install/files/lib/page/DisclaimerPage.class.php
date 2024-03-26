<?php

namespace wcf\page;

use wcf\page\AbstractPage;
use wcf\system\exception\NamedUserException;
use wcf\system\user\authentication\configuration\UserAuthenticationConfigurationFactory;
use wcf\system\WCF;

/**
 * Shows the disclaimer.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class DisclaimerPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $neededModules = ['REGISTER_ENABLE_DISCLAIMER'];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        // registration disabled
        if (
            !WCF::getUser()->userID
            && !UserAuthenticationConfigurationFactory::getInstance()->getConfigration()->canRegister
        ) {
            throw new NamedUserException(WCF::getLanguage()->get('wcf.user.register.error.disabled'));
        }
    }
}
