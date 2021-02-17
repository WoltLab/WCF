<?php

namespace wcf\system\event\listener;

use wcf\system\email\SimpleEmail;
use wcf\system\user\authentication\UserLoggedIn;

/**
 * Sends a notification email when the user logs in.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Event\Listener
 */
final class UserLoggedInEmailNotificationListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     * @param UserLoggedIn $eventObj
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        $email = new SimpleEmail();
        $email->setRecipient($eventObj->getUser());
        $email->setSubject("You logged in");
        $email->setMessage("You logged in");
        $email->setHtmlMessage("You logged in");
        $email->send();
    }
}
