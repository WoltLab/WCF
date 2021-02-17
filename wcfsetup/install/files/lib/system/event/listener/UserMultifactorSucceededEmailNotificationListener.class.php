<?php

namespace wcf\system\event\listener;

use wcf\system\email\SimpleEmail;
use wcf\system\user\authentication\UserMultifactorSucceeded;

/**
 * Sends a notification email when the user performs MF authentication.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Event\Listener
 */
final class UserMultifactorSucceededEmailNotificationListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     * @param UserMultifactorSucceeded $eventObj
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        $email = new SimpleEmail();
        $email->setRecipient($eventObj->getUser());
        $email->setSubject("You performed MFA with {$eventObj->getSetup()->getObjectType()->objectType}");
        $email->setMessage("You performed MFA with {$eventObj->getSetup()->getObjectType()->objectType}");
        $email->setHtmlMessage("You performed MFA with {$eventObj->getSetup()->getObjectType()->objectType}");
        $email->send();
    }
}
