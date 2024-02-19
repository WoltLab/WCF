<?php

/**
 * Generate new private/public key pair for the service worker.
 *
 * @author Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\service\worker\ServiceWorkerHandler;

ServiceWorkerHandler::getInstance()->updateKeys();
