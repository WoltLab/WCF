<?php

namespace wcf\system\session;

use wcf\system\event\EventHandler;

/**
 * Handles the ACP legacy session of the active user.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ACPSessionFactory
{
    /**
     * Loads the object of the active session.
     *
     * @return void
     */
    public function load()
    {
        SessionHandler::getInstance()->loadFromCookie();

        // call beforeInit event
        if (!\defined('NO_IMPORTS')) {
            EventHandler::getInstance()->fireAction($this, 'beforeInit');
        }

        $this->init();

        // call afterInit event
        if (!\defined('NO_IMPORTS')) {
            EventHandler::getInstance()->fireAction($this, 'afterInit');
        }
    }

    /**
     * Initializes the session system.
     *
     * @return void
     */
    protected function init()
    {
        SessionHandler::getInstance()->initSession();
    }
}
