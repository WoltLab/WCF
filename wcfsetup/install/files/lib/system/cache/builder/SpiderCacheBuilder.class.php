<?php

namespace wcf\system\cache\builder;

use wcf\data\spider\SpiderList;

/**
 * Caches the list of search engine spiders.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Cache\Builder
 */
class SpiderCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        $spiderList = new SpiderList();
        $spiderList->sqlOrderBy = "spider.spiderID ASC";
        $spiderList->readObjects();

        if (isset($parameters['fastLookup'])) {
            $firstCharacter = [];
            $mapping = [];
            foreach ($spiderList as $spider) {
                if (!isset($firstCharacter[$spider->spiderIdentifier[0]])) {
                    $firstCharacter[$spider->spiderIdentifier[0]] = [];
                }
                $firstCharacter[$spider->spiderIdentifier[0]][] = \substr($spider->spiderIdentifier, 1);

                $mapping[$spider->spiderIdentifier] = $spider->spiderID;
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
                'regex' => "/{$regex}/",
                'mapping' => $mapping,
            ];
        }

        return $spiderList->getObjects();
    }
}
