<?php

namespace wcf\system\spider;

use wcf\event\spider\SpiderCollecting;
use wcf\system\cache\eager\SpiderCache;
use wcf\system\event\EventHandler;
use wcf\system\SingletonFactory;

/**
 * Handles spider related operations.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class SpiderHandler extends SingletonFactory
{
    /**
     * @var Spider[]
     */
    private array $spiders;

    /**
     * Returns the spider with the given identifier.
     */
    public function getSpider(string $identifier): ?Spider
    {
        // The spiders are only needed to display the users online, they are not
        // required to detect a spider.
        if (!isset($this->spiders)) {
            $event = new SpiderCollecting();
            EventHandler::getInstance()->fire($event);

            $this->spiders = $event->getSpiders();
        }

        return $this->spiders[$identifier] ?? null;
    }

    /**
     * Finds the spider identifier for the given user agent.
     */
    public function getIdentifier(string $userAgent): ?string
    {
        $regex = (new SpiderCache())->getCache()['regex'];

        $userAgent = \mb_strtolower($userAgent);
        if (\preg_match($regex, $userAgent, $matches)) {
            return $matches[0];
        }

        return null;
    }
}
