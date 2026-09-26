<?php

namespace wcf\system\cache\eager;

use wcf\event\spider\SpiderCollecting;
use wcf\system\event\EventHandler;

/**
 * Eager cache implementation for the regex that detects spiders by their user agent.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @extends AbstractEagerCache<array{regex: string}>
 */
final class SpiderCache extends AbstractEagerCache
{
    #[\Override]
    protected function getCacheData(): array
    {
        $event = new SpiderCollecting();
        EventHandler::getInstance()->fire($event);

        $firstCharacter = [];
        foreach ($event->getSpiders() as $identifier => $spider) {
            if (!isset($firstCharacter[$identifier[0]])) {
                $firstCharacter[$identifier[0]] = [];
            }
            $firstCharacter[$identifier[0]][] = \substr($identifier, 1);
        }

        $regex = '';
        foreach ($firstCharacter as $char => $spiders) {
            if ($regex !== '') {
                $regex .= '|';
            }
            $regex .= \sprintf(
                '(?:%s(?:%s))',
                \preg_quote($char, '/'),
                \implode('|', \array_map(static function ($identifier) {
                    return \preg_quote($identifier, '/');
                }, $spiders))
            );
        }

        if ($regex === '') {
            // This regex will never match anything.
            $regex = '(?!)';
        }

        return [
            'regex' => '/' . $regex . '/',
        ];
    }
}
