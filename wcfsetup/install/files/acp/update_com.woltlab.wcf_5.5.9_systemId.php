<?php

/**
 * Reset the System ID to encourage a recheck for the OPCache check.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\Environment;
use wcf\system\registry\RegistryHandler;

RegistryHandler::getInstance()->delete(
    'com.woltlab.wcf',
    Environment::SYSTEM_ID_REGISTRY_KEY
);
