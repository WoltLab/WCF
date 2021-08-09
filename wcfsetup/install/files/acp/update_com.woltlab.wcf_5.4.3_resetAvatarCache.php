<?php

/**
 * Resets the user storage cache for avatars. These had not been updated after
 * generating the WebP variants of avatars, causing the "old" avatar to be
 * presented to the browser despite WebP being available.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\user\storage\UserStorageHandler;

UserStorageHandler::getInstance()->resetAll('avatar');
