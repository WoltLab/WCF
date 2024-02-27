<?php

namespace wcf\system\spider;

use wcf\system\event\EventHandler;
use wcf\system\SingletonFactory;
use wcf\system\spider\event\SpiderCollecting;

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
    private array $spiders = [];

    private string $regex = '';

    #[\Override]
    protected function init()
    {
        parent::init();
        $event = new SpiderCollecting();
        EventHandler::getInstance()->fire($event);

        $this->spiders = $event->getSpiders();

        $firstCharacter = [];
        foreach ($this->spiders as $identifier => $spider) {
            if (!isset($firstCharacter[$identifier[0]])) {
                $firstCharacter[$identifier[0]] = [];
            }
            $firstCharacter[$identifier[0]][] = \substr($identifier, 1);
        }

        $this->regex = '';
        foreach ($firstCharacter as $char => $spiders) {
            if ($this->regex !== '') {
                $this->regex .= '|';
            }
            $this->regex .= \sprintf(
                '(?:%s(?:%s))',
                \preg_quote($char, '/'),
                \implode('|', \array_map(static function ($identifier) {
                    return \preg_quote($identifier, '/');
                }, $spiders))
            );
        }

        if ($this->regex === '') {
            // This regex will never match anything.
            $this->regex = '(?!)';
        }
        $this->regex = '/' . $this->regex . '/';
    }

    /**
     * Returns the spider with the given identifier.
     */
    public function getSpider(string $identifier): ?Spider
    {
        return $this->spiders[$identifier] ?? null;
    }

    /**
     * Finds the spider identifier for the given user agent.
     */
    public function getIdentifier(string $userAgent): ?string
    {
        $userAgent = \mb_strtolower($userAgent);
        if (\preg_match($this->regex, $userAgent, $matches)) {
            return $matches[0];
        }

        return null;
    }
}
