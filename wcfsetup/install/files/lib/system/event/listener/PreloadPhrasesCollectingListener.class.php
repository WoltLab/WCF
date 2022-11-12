<?php

namespace wcf\system\event\listener;

use wcf\system\language\preload\event\PreloadPhrasesCollecting;

/**
 * Registers a set of default phrases for preloading.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Event\Listener
 * @since 6.0
 */
final class PreloadPhrasesCollectingListener
{
    public function __invoke(PreloadPhrasesCollecting $event): void
    {
        $event->preload('wcf.date.relative.now');
    }
}
