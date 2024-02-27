<?php

namespace wcf\system\spider\event;

use wcf\system\event\IEvent;
use wcf\system\spider\Spider;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class SpiderCollecting implements IEvent
{
    /**
     * @var Spider[]
     */
    private array $spiders = [];

    /**
     * Registers a spider.
     */
    public function register(Spider $spider): void
    {
        $identifier = \mb_strtolower($spider->identifier);
        if (\array_key_exists($identifier, $this->spiders)) {
            throw new \InvalidArgumentException('Spider with identifier ' . $identifier . ' already exists');
        }
        $this->spiders[$identifier] = $spider;
    }

    /**
     * @return Spider[]
     */
    public function getSpiders(): array
    {
        return $this->spiders;
    }
}
